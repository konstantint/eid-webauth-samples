from flask import Flask, request, session, redirect, flash, render_template

# Configure the app
app = Flask(__name__)
app.secret_key = 'secret key for cookie signing'

# Set up views
@app.route('/')
def index():
    return render_template('index.html', user=session.get('user', None))

@app.route('/login')
def login():
    user = request.headers.get('SSL_CLIENT_S_DN_CN')
    if user is not None and user != '':
        # TODO: OCSP validation
        session['user'] = user
    return redirect('/')

@app.route('/logout')
def logout():
    del session['user']
    flash('You might need to restart the browser and take your card out to ensure proper logout')
    return redirect('/')


# Wrap with middleware that will automatically replace scheme with https
# so that redirects would work
class URLProtocolFix(object):
    def __init__(self, app):
        self.app = app
    def __call__(self, environ, start_response):
        environ['wsgi.url_scheme'] = 'https'
        return self.app(environ, start_response)

app.wsgi_app = URLProtocolFix(app.wsgi_app)

# Main method for debugging
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)

# WSGI endpoint
wsgi = app.wsgi_app


