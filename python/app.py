from flask import Flask, session, redirect, url_for, escape, request, render_template
import requests
import json
app = Flask(__name__)

APP_ID = '8c9253dca23777745c9102e0be99ea70'
APP_SECRET = 'a9a356f16c77bdcddf15f0a1c407dd3a'
app.secret_key = 'A0Zr98j/3yX R~XHH!jmN]LWX/,?RT'

@app.route("/")
def hello():
    user = session.get('user', None)
    #email = json.loads(user)['info']['email'] if user else None
    email = user
    return render_template('index.html', user=user, email=email)

@app.route('/login')
def login():
  code = request.args.get('code')
  data = {'app_id': APP_ID, 'app_secret': APP_SECRET, 'code': code}
  r = requests.post('https://clef.io/api/authorize', data=data)
  r = json.loads(r.text)
  print r
  token = r['access_token']
  data = {'access_token' : token}
  r = requests.get('https://clef.io/api/info', data=data)
  session['user'] = r.text
  return redirect(url_for('hello'))

if __name__ == "__main__":
    app.run(port=5000,debug=True)
