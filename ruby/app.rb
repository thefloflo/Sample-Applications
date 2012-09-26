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

APP_ID = 'fb648ef625383d0fc08540b35eecb0ef'
APP_SECRET = '9573feea89dc1c7b762d370d6d6dd5b0'

get '/' do
  @user = session[:user]
  erb :index
end

get '/login' do
  code = params[:code]
  response = JSON.parse(HTTParty.get("https://clef.io/api/authorize?code=#{code}&app_id=#{APP_ID}&app_secret=#{APP_SECRET}").body)
  if response['success']
    token = response['access_token']
    response = JSON.parse(HTTParty.get("https://clef.io/api/info?access_token=#{token}").body)
    session[:user] = response
    redirect '/'
  else
    return response.to_json
  end
end