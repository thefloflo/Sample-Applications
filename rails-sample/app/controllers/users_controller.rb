require 'httparty'
class UsersController < ApplicationController

  def new
  end

  def create
    puts auth_hash
    @user = User.find_or_create_from_auth_hash(auth_hash)
    binding.pry
    redirect_to user_path(@user)
  end

  def show
    @user = User.find(params[:id])
  end

  protected
    def auth_hash
        request.env['omniauth.auth']
    end
end
