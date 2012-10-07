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
  response = JSON.parse(HTTPParty.post("https://clef.io/api/authorize", :query => {:code => code, :app_id => APP_ID, :app_secret => APP_SECRET}).body)
  if response['success']
    token = response['access_token']
    response = JSON.parse(HTTParty.post("https://clef.io/api/info", :query => {:access_token => token}).body)
    session[:user] = response
    redirect '/'
  else
    return response.to_json
  end
end
