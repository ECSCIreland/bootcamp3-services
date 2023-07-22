from flask import Flask, request, render_template, session
from flask_wtf.csrf import CSRFProtect
from src.auth import auth_login, auth_register, auth_logout
from src.prescription import register_medicine, list_medicines, request_prescription, buy_medicine, medicine_details
from src.appointments import message_appointment, schedule_appointment, details_appointment, list_doctors
from Crypto.Util.number import *
import os

app = Flask(__name__)

# This could probably be made more secure by importing os and setting app.secret.key as an env variable, 
# a dirty fix would be something like
# e.g:
# app.secret_key = ':,4,XL,K(yB%H2!£S@Dz#Q&qf~}?NI29blb:,4,XL,K(yB%H2!£S@Dz#Q&qf~}?NI29blb:,4,XL,K(yB%H2!£S@Dz#Q&qf~}?NI29blb'
app.secret_key= os.environ['SECRET_KEY']  # ChatGPT: Set a secret key for session security

csrf = CSRFProtect()
csrf.init_app(app)
@app.route('/')
def index():
    if 'username' not in session:
        return render_template('login.html')
    elif session['role'] == 'patient':
        return render_template('patient.html', role=session['role'])
    elif session['role'] == 'supplier':
        return register_medicine(request)
    elif session['role'] == 'doctor':
        return message_appointment(request)

# Authentication endpoints
@app.route('/auth/login', methods=['POST', 'GET'])
def login():
    return auth_login(request)

@app.route('/auth/register', methods=['POST', 'GET'])
def register():
    return auth_register(request)

@app.route('/auth/logout', methods=['POST', 'GET'])
def logout():
    return auth_logout(request)
# ---------------------------------------


# Flag subscription endpoints
@app.route('/medicine/register', methods=['POST', 'GET'])
def medicine_register():
    return register_medicine(request)

@app.route('/medicine/list', methods=['POST', 'GET'])
def medicine_list():
    return list_medicines(request)

@app.route('/medicine/request', methods=['POST', 'GET'])
def medicine_request():
    return request_prescription(request)

@app.route('/medicine/buy', methods=['POST', 'GET'])
def medicine_buy():
    return buy_medicine(request)

@app.route('/medicine/details', methods=['POST'])
def get_medicine():
    return medicine_details(request)
# ---------------------------------------


# Appointment scheduling endpoints
@app.route('/appointment/message', methods=['POST', 'GET'])
def appointment_message():
    return message_appointment(request)

@app.route('/appointment/schedule', methods=['POST', 'GET'])
def appointment_schedule():
    return schedule_appointment(request)

@app.route('/appointment/details', methods=['POST', 'GET'])
def appointment_details():
    return details_appointment(request)

@app.route('/doctor/list', methods=['POST', 'GET'])
def doctors_list():
    return list_doctors(request)
# ---------------------------------------


# debug should never be set as True in a prod env.
if __name__ == '__main__':
    app.run(port=5001)
