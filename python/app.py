from flask import Flask, session, redirect, url_for, escape, request, render_template
import requests
import json
app = Flask(__name__)

APP_ID = '360cc9edd45ecc428ca8c3652b5effee'
APP_SECRET = '603bd6e189c7594f6697faee26a4fef0'
app.secret_key = 'A0Zr98j/3yX R~XHH!jmN]LWX/,?RT'

@app.route("/")
def hello():
    user = session.get('user', None)
    email = json.loads(user)['info']['email'] if user else None
    return render_template('index.html', user=user, email=email)
    
@app.route('/login')
def login():
  code = request.args.get('code')
  r = requests.get('http://clef.io/api/authorize?code=%s&app_id=%s&app_secret=%s' % (code, APP_ID, APP_SECRET), verify=False)
  r = json.loads(r.text)
  token = r['access_token']
  r = requests.get('https://clef.io/api/info?access_token=%s' % token, verify=False)
  session['user'] = r.text
  return redirect(url_for('hello'))

if __name__ == "__main__":
    app.run(port=9292,debug=True)
