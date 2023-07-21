'''
    API for prescription
'''
from flask import render_template, session, redirect, url_for
from werkzeug.utils import secure_filename
from hashlib import sha256
# swap from md5 to sha256. Probably not needed but safer than md5
import base64

from src.auth import login_required

import json
import os

all_ids = set()
all_names = set()

def load_medicine_by_batch_id(supplier, batch_id):
    h_batch_id = sha256(batch_id.encode()).hexdigest()
    return load_medicine(supplier, h_batch_id+'.json')

def load_medicine(supplier, medicine):
    path = secure_filename(f'medicines/{supplier}/{medicine}')
    if os.path.exists(path):
        with open(path, mode='r') as medicine_file:
            medicine = json.loads(medicine_file.read())
            if os.path.exists(medicine['image']):
                image_data = open(secure_filename(medicine['image']), mode='rb').read()
            else:
                image_data = b'Could not find image!'
            return medicine, image_data
    return None, None

for supplier in os.listdir('medicines'):
    if os.path.isdir(f'medicines/{supplier}'):
        for medicine in os.listdir(f'medicines/{supplier}'):
            if medicine.endswith('.json'):
                try:
                    med, img = load_medicine(supplier, medicine)
                    if med is not None and img is not None:
                        all_ids.add(med['batch_id'])
                        all_names.add(med['name'])
                except:
                    print('[-] Failed to load medicine during startup: ', medicine)

@login_required
def register_medicine(request):
    if session['role'] != 'supplier':
        return redirect(url_for('index'))
    
    supplier_name = session['username']
    if request.method == 'POST':
        # Handle form
        medicine_name = request.form['medicine_name']
        medicine_description = request.form['description']
        needs_approval = request.form.get('needs_approval', 'off')
        batch_id = request.form.get('batch_id')
        medicine_usage = request.form['usage']
        image = request.files['image']
        if len(image.read()) > 500 * 1024:
            return render_template('medicine_register.html', error='Image file too large!')            
        try:
            _ = int(batch_id)
        except:
            return render_template('medicine_register.html', error='This is not a valid batch ID!')
        
        if int(batch_id) > 2**256 - 1:
            return render_template('medicine_register.html', error='batch ID too high!')
        
        if batch_id in all_ids:
            return render_template('medicine_register.html', error='Medicine ID already exists!')

        if medicine_name in all_names:
            return render_template('medicine_register.html', error='Medicine name already exists!')

        if '.jpg' not in image.filename:
            return render_template('medicine_register.html', error='Invalid image format!')
        
        filename = f'medicines/{supplier_name}/{secure_filename(image.filename)}'
        image.seek(0)
        image.save(filename)

        medicine = {}
        medicine['name'] = medicine_name
        medicine['batch_id'] = batch_id
        all_ids.add(batch_id)
        all_names.add(medicine_name)
        
        medicine['description'] = medicine_description
        medicine['needs_approval'] = needs_approval
        medicine['usage'] = medicine_usage
        medicine['image'] = filename
        medicine['key'] = session['key']

        h_batch_id = sha256(batch_id.encode()).hexdigest()
        with open(f'medicines/{supplier_name}/{h_batch_id}.json', mode='w') as medicine_file:
            medicine_file.write(json.dumps(medicine))

        N, e, d = session['key']
        signature = pow(int(batch_id), d, N)

        return render_template('medicine_register.html', success=f'Medicine registered successfully: {signature}', role='supplier')
    else:
        return render_template('medicine_register.html')

def get_medicines(supplier):
    try:
        medicines = os.listdir(f'medicines/{supplier}')
    except:
        return []
    result = []
    for medicine in medicines:
        if medicine.endswith('.json'):
            med, img = load_medicine(supplier, medicine)
            
            if med is not None and img is not None:
                result.append((med['name'], med['batch_id'], med['needs_approval'], med['description']))#, base64.b64encode(img).decode()))
    return result

def get_medicine(supplier, batch_id):
    medicines = os.listdir(f'medicines/{supplier}')

    for medicine in medicines:
        if medicine.endswith('.json'):
            med, img = load_medicine(supplier, medicine)
            if med['batch_id'] == batch_id:
                if med is not None and img is not None:
                    return (med['name'], med['batch_id'], med['needs_approval'], med['description'], med['usage'], base64.b64encode(img).decode())
    return None

@login_required
def list_medicines(request):
    if session['role'] == 'supplier':
        supplier = session['username']
        medicines = get_medicines(supplier)
        return render_template('medicine_list.html', role=session['role'], medicines=medicines, supplier=supplier)
    if session['role'] != 'patient':
        return redirect(url_for('index'))
   
    if request.method == 'POST':
        error = None
        supplier = request.form['supplier']
        medicines = get_medicines(supplier)
        if len(medicines) == 0:
            error = 'No medicines found from this supplier'
            return render_template('list_medicines.html', supplier=supplier, error=error)
        return render_template('medicine_list.html', medicines=medicines, supplier=supplier, role=session['role'])
    else:
        return render_template('list_medicines.html')

@login_required
def request_prescription(request):
    if session['role'] != 'patient':
        return redirect(url_for('index'))
   
    if request.method == 'POST':
        supplier = request.form['supplier']
        batch_id = request.form['batch_id']

        med, img = load_medicine_by_batch_id(supplier, batch_id)
        if med is None or img is None:
            return render_template('request_prescription.html', error='Medicine not found!')
        if med['needs_approval'] == 'on':
            return render_template('request_prescription.html', error='You need approval from a specialist for that!', role='patient')
        
        # Sign prescription
        N, _, d = med['key']
        signature = pow(int(med['batch_id']), d, N)

        return render_template('request_prescription.html', success=f'Prescription for {med["name"]}: {signature}')
            
    else:
        return render_template('request_prescription.html')

@login_required
def buy_medicine(request):
    if session['role'] != 'patient':
        return redirect(url_for('index'))
   
    if request.method == 'POST':
        supplier = request.form['supplier']
        batch_id = request.form['batch_id']
        prescription = request.form['prescription']

        if not prescription.isdigit():
            return render_template('buy_medicine.html', error='This is not a valid prescription!')
        
        med, img = load_medicine_by_batch_id(supplier, batch_id)
        if med is None or img is None:
            return render_template('buy_medicine.html', error='Medicine not found!')
        N, e, _ = med['key']
        if pow(int(prescription), e, N) == int(med['batch_id']):
            instruction = f'You bought medicine {med["name"]}. Please use it according to the instructions: {med["usage"]}'
            return render_template('buy_medicine.html', message=instruction)
        else:
            return render_template('buy_medicine.html', error=f'Invalid prescription!')
    else:
        return render_template('buy_medicine.html')

@login_required
def medicine_details(request):
    supplier = request.form['supplier']
    batch_id = request.form['batch_id']
    if session['role'] != 'patient' and session['username'] != supplier:
        return redirect(url_for('index'))

    medicine = get_medicine(supplier, batch_id)

    if medicine is None:
        return render_template('patient.html', error='Error loading medicines')
    
    if session['role'] == 'supplier' and session['username'] == supplier:
        return render_template('medicine_details.html', role=session['role'], name=medicine[0], batch_id=medicine[1], approv=medicine[2], desc=medicine[3], instruction= medicine[4], img=medicine[5])
    return render_template('medicine_details.html', role=session['role'], name=medicine[0], batch_id=medicine[1], approv=medicine[2], desc=medicine[3], img=medicine[5])
