require 'rubygems'
require 'sinatra'
require 'httparty'
require 'json'
require 'pry'

enable :sessions, :logging
set :raise_errors, false
set :show_exceptions, false
set :environment, :development
set :root, File.dirname(__FILE__)

APP_ID = '6f8fb6e642924a5e9e7deacf35292abf'
APP_SECRET = '27788a6c0934331258651af709813ba2'

get '/' do
  @user = session[:user]
  erb :index
end

get '/login' do
  code = params[:code]
  response = HTTParty.post("https://clef.io/api/v1/authorize", { body: { code: code, app_id: APP_ID, app_secret: APP_SECRET} })
  if response['success']
    response = HTTParty.get("https://clef.io/api/v1/info?access_token=#{response['access_token']}")
    session[:user] = response.body
    redirect '/'
  else
    return response.to_json
  end
end
