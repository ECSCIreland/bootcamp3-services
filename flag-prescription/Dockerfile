FROM python:3.11

WORKDIR /app

# Copy the application code into the container
COPY . .

# Install any needed packages specified in requirements.txt
RUN pip install --trusted-host pypi.python.org -r requirements.txt

# Expose the port that the app will listen on
EXPOSE 5001

# Set the environment variable for Flask
ENV FLASK_APP=main.py

# Run the command to start the Flask application
CMD ["flask", "run", "--host=0.0.0.0"]
