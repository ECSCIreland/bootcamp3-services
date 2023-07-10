FROM python:3.9

# Downloaded from https://github.com/jazzband/django-revproxy/archive/refs/tags/0.11.0.zip
COPY 0.11.0.zip /tmp

RUN pip install --no-cache-dir --upgrade django gunicorn /tmp/0.11.0.zip

# Patch because apache doesnt like underscores in headers anymore
RUN sed -ri -e "s!'REMOTE_USER'!'REMOTE-USER'!g" /usr/local/lib/python3.9/site-packages/revproxy/views.py

COPY src /app
COPY gunicorn_conf.py /app
WORKDIR /app
RUN python3 manage.py migrate
CMD ["gunicorn", "--conf", "gunicorn_conf.py", "--bind", "0.0.0.0:80", "--reload", "authproxy.wsgi"]