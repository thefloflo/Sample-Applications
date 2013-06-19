from flask import Flask, session, redirect, url_for, escape, request, render_template
from flask.ext.sqlalchemy import SQLAlchemy
import requests
import json
import time

app = Flask(__name__)

SQLALCHEMY_DATABASE_URI = 'sqlite:////tmp/test.db'
DEBUG = True
APP_ID = '8c9253dca23777745c9102e0be99ea70'
APP_SECRET = 'a9a356f16c77bdcddf15f0a1c407dd3a'
app.secret_key = 'A0Zr98j/3yX R~XHH!jmN]LWX/,?RT'

class User(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    email = db.Column(db.String())
    first_name = db.Column(db.String())
    clef_id = db.Column(db.Integer, unique=True)
    logged_out_at = db.Column(db.Integer)

@app.route("/")
def hello():
    user = session.get('user', None)
    email = user

    return render_template('index.html', user=user, email=email)

@app.route('/members_area')
def members_area():
    if session.get('user', None) is None:
        redirect(url_for("/"))
    elif session.get('logged_in_at', 0) < User.query.filter_by(clef_id=session['user']).first()['logged_out_at']:
        session.clear()

        redirect(url_for("/"))

    return render_template("members_area.html", name=User.query.filter_by(clef_id=session['user']).first()['first_name'])

@app.route('/login')
def login():
    code = request.args.get('code')
    data = dict(app_id=APP_ID, app_secret=APP_SECRET,code=code)

    response = requests.post('https://clef.io/api/v1/authorize', data=data)
    json_response = json.loads(response.text)

    if json_response.get('error'):
        return json_response['error']

    token = json_response['access_token']
    response = requests.get('https://clef.io/api/v1/info?access_token=%s' % token)
    json_response = json.loads(response.text)

    if json_response.get('error'):
        return json_response['error']

    user_info = json_response['info']
    user = User.query.filter_by(clef_id=user_info['id']).first()
    if not user:
        user = User(email=user_info['email'],
            first_name=user_info['first_name'],
            clef_id=user_info['id'])
        db.session.add(user)
        db.session.commit()

    session['user'] = user.id
    session['logged_in_at'] = time.time()

    return redirect(url_for('/members_area'))

@app.route("/logout_hook", methods=['POST'])
def logout_hook:
    if request.form.get("logout_token", None) is not None:
        data = dict(logout_token=request.form.get("logout_token"), app_id=APP_ID, app_secret=APP_SECRET)

        response = requests.post("https://clef.io/api/v1/logout", data=data)

        if response.status_code == 200:
            json_response = json.loads(response.text)

            if json_response.get('success', False):
                clef_id = json_response.get('clef_id', None)

                # add logged_out_at to db

    return "ok"


if __name__ == "__main__":
    app.run(port=5000,debug=True)
