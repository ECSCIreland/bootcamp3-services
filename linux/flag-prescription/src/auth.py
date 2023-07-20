'''
    Login/Logout/Register functionality
'''

from flask import render_template, session, redirect, url_for
from Crypto.Util.number import *
from functools import wraps
import sqlite3
import os
import hashlib

DATABASE = 'database/auth.db'

def invalid_specialty(specialty):
    if specialty not in ['Family Medicine Physician', 'Pediatrician', 'Obstetrician', 'Cardiologist', 'Pharmacist', 'Dermatologist', 'Psychiatrist', 'Surgeon']:        
        return True
    return False
def invalid_name(name):
    ''' All characters need to be alphanumeric '''
    return not name.isalnum()

def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'username' not in session:
            return redirect(url_for("index"))
        return f(*args, **kwargs)
    return decorated_function

def connect_database():
    if not os.path.exists(DATABASE):
        # create a new database file
        conn = sqlite3.connect(DATABASE)

        # create a cursor object to execute SQL queries
        cur = conn.cursor()

        with open('database/init.sql') as f:
            queries = f.read().split(';')
        
        for query in queries:
            cur.execute(query + ';')

        # commit the changes to the database
        conn.commit()
    else:
        conn = sqlite3.connect(DATABASE)
        cur = conn.cursor()
    return conn, cur

def auth_login(request):
    if request.method == 'POST':
        name = request.form['name']
        password = request.form['password']

        conn, cur = connect_database()
        cur = conn.cursor()
        cur.execute("SELECT role, password, n, e, d FROM users WHERE username=?", (name,))
        pwd = cur.fetchone()
        if pwd is None or invalid_name(name):
            error = 'Invalid username or password!'
            return render_template('login.html', error=error)
    
        role, pwd, n, e, d = pwd

        n = int(n, 16)
        e = int(e, 16)
        d = int(d, 16)
        
        if pwd != hashlib.sha256(password.encode()).hexdigest():
            return render_template('login.html', error='Wrong password!', role='guest')

        session['username'] = name
        session['key'] = (n, e, d)
        session['role'] = role

        return redirect(url_for("index"))
    else:
        return render_template('login.html')

def auth_register(request):
    if request.method == 'POST':
        name = request.form['name']
        
        password = request.form['password']
        password_confirm = request.form['password-confirm']
        if password != password_confirm:
            return render_template('login.html', error='Non identical password values!')
        role = request.form['role'].lower()

        if role not in ['supplier', 'doctor', 'patient']:
            return render_template('login.html', error='Invalid role!')

        if invalid_name(name):
            return render_template('login.html', error='Invalid username!')
        
        conn, cur = connect_database()
        cur = conn.cursor()
        cur.execute(f"SELECT username FROM users WHERE username=?", (name,))
        if cur.fetchone() is not None:
            return render_template('login.html', error='User already exists')

        if role =='doctor':
            fname = request.form['first-name']
            lname = request.form['last-name']
            specialty = request.form['specialty']
            
            is_private = True if request.form.get('private-doctor', 'off') == 'on' else False 
            if invalid_name(fname) or invalid_name(lname):
                return render_template('login.html', error='Invalid name!')
            if specialty not in ["Family Medicine Physician", "Pediatrician", "Obstetrician", "Cardiologist", "Pharmacist", "Dermatologist", "Psychiatrist", "Surgeon"]:
                return render_template('login.html', error='Unknown specialty!')
            cur.execute(f"INSERT INTO doctor_attributes (username, fname, lname, specialty, message, is_private) VALUES (?, ?, ?, ?, ?, ?)", (name, fname, lname, specialty, 'Initial Message!', is_private))
            conn.commit()

        # Create user
        p = getPrime(512)
        q = getPrime(512)
        N = p*q
        e = 65537
        d = inverse(e, (p-1)*(q-1))

        password = hashlib.sha256(password.encode()).hexdigest()
        cur.execute(f"INSERT INTO users VALUES (?, ?, ?, ?, ?, ?)", (role, name, password, hex(N), hex(e), hex(d)))
        conn.commit()
        
        session['key'] = (N, e, d)

        session['role'] = role
        session['username'] = name
        if role == 'supplier':
            assert not os.path.exists(f'medicines/{name}')
            os.mkdir(f'medicines/{name}')

        return redirect(url_for("index"))
    else:
        return render_template('register.html')

def auth_logout(request):
    session.pop('username', None)
    session.pop('role', None)
    session.pop('key', None)
    return redirect(url_for('index'))