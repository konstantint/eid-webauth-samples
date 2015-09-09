#!/bin/bash

service apache2 start
cd /eid-python-sample
exec gunicorn -b localhost:5000 app:wsgi

