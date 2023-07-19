from Crypto.Cipher import AES
from flask import render_template, session, redirect, url_for
from Crypto.Random import get_random_bytes
from Crypto.Util.Padding import pad, unpad
from src.auth import login_required, connect_database, invalid_name, invalid_specialty
from src.appointment_pb2 import Appointment
'''
    API for appointments
'''
appointment_key =  get_random_bytes(AES.block_size)

@login_required
def message_appointment(request):
    if session['role'] != 'doctor':
        return redirect(url_for('index'))
    
    conn, cur = connect_database()
    cur = conn.cursor()
    
    if request.method == 'POST':
        fname = request.form['first-name']
        lname = request.form['last-name']
        new_message = request.form['message']
        specialty = request.form['specialty']
        is_private = request.form.get('is_private', False)
        username = session['username']
        if invalid_name(fname + lname) or invalid_specialty(specialty):
            error = 'Invalid characters identified!'
            cur.execute(f"SELECT fname, lname, message, specialty, is_private FROM doctor_attributes WHERE username=?", (session['username'],))
            dt = cur.fetchone()
            return render_template('appointment_message.html', fname=dt[0], lname=dt[1], message=dt[2], specialty=dt[3], is_private=dt[4], error=error)

        cur.execute(f"UPDATE doctor_attributes SET fname=?, lname=?, message=?, specialty=? WHERE username=?", (fname, lname, new_message, specialty, username))
        conn.commit()
        iv = request.form['csrf_token'][-16:].encode()
        
        cipher = AES.new(appointment_key, AES.MODE_CBC, iv)
        appointment = Appointment()
        appointment.notes = b''
        appointment.datetime = b''
        appointment.doctor = session['username'].encode()
        appointment.patient = b'Dummy Patient'
        appointment_data = appointment.SerializeToString()
        ct = iv.hex() + cipher.encrypt(pad(appointment_data, AES.block_size)).hex()
        return render_template('appointment_message.html', fname=fname, lname=lname, specialty=specialty, message=new_message, is_private=is_private, success='Your information has been updated! Sample token: ' + ct)
    else:
        cur.execute(f"SELECT fname, lname, message, specialty, is_private FROM doctor_attributes WHERE username=?", (session['username'],))
        dt = cur.fetchone()
        return render_template('appointment_message.html', fname=dt[0], lname=dt[1], message=dt[2], specialty=dt[3], is_private=dt[4])

@login_required
def schedule_appointment(request):
    if session['role'] != 'patient':
        return redirect(url_for('index'))
    if request.method == 'POST':
        notes = request.form['notes']
        if len(notes) > 50:
            return render_template('appointment_schedule.html', error='The doctor is very busy for long notes!')
        doctor = request.form['doctor']
        datetime = request.form['appointment']
        conn, cur = connect_database()
        cur = conn.cursor()
        cur.execute(f"SELECT * FROM doctor_attributes WHERE username=?", (doctor,))
        res = cur.fetchone()
        if res is None:
            error = 'This doctor does not exist in the database!'
            return render_template('appointment_schedule.html', error=error)
        if res[6]:
            error = 'Only for premium and VIP patients!'
            return render_template('appointment_schedule.html', error=error)
        iv = request.form['csrf_token'][-16:].encode()
        
        cipher = AES.new(appointment_key, AES.MODE_CBC, iv)
        appointment = Appointment()
        appointment.notes = notes.encode()
        appointment.datetime = datetime.encode()
        appointment.doctor = doctor.encode()
        appointment.patient = session['username'].encode()
        appointment_data = appointment.SerializeToString()
        ct = iv.hex() + cipher.encrypt(pad(appointment_data, AES.block_size)).hex()
        
        message = 'You can use the following token to access the details of your appointment!\n' + ct
        message += '\nThe information you entered is stored on this object:\n' + appointment_data.hex()
        return render_template('appointment_schedule.html', success=message)
    else:
        return render_template('appointment_schedule.html')

@login_required
def details_appointment(request):
    if session['role'] != 'patient':
        return redirect(url_for('index'))
    if request.method == 'POST':
        try:
            token = request.form['token']
            iv = bytes.fromhex(token[:32])
            ct = bytes.fromhex(token[32:])
            
            cipher = AES.new(appointment_key, AES.MODE_CBC, iv)
            pt = unpad(cipher.decrypt(ct), AES.block_size)
            appointment = Appointment()
            appointment.ParseFromString(pt)
            doctor = appointment.doctor.decode()
            conn, cur = connect_database()
            cur = conn.cursor()
            cur.execute(f"SELECT message FROM doctor_attributes WHERE username=?", (doctor,))
            message = cur.fetchone()[0]
            datetime = appointment.datetime
        except:
            message = 'An error has occured!'
            return render_template('appointment_details_form.html', error=message)    
        return render_template('appointment_details.html', message=message, doctor=doctor, datetime=datetime.decode())
    else:
        return render_template('appointment_details_form.html')

@login_required
def list_doctors(request):
    if session['role'] != 'patient':
        return redirect(url_for('index'))
    
    conn, cur = connect_database()
    cur = conn.cursor()
    if request.method == 'POST':
        username = request.form['name']
        if invalid_name(username):
            return render_template('doctors.html', error='Invalid username!')
        
        cur.execute(f"SELECT username, fname, lname, specialty, is_private FROM doctor_attributes WHERE username=?", (username,))
        doctors = cur.fetchall()
        return render_template('doctors.html', doctors=doctors)
    else:
        cur.execute(f"SELECT username, fname, lname, specialty, is_private FROM doctor_attributes ORDER BY id DESC LIMIT 10",)
        doctors = cur.fetchall()
        return render_template('doctors.html', doctors=doctors)
