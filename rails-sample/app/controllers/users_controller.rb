require 'httparty'
class UsersController < ApplicationController
  CLEF_ID = 'bc7b3e3fa9ac6ad3f97df4bb03a5a0f6'
  CLEF_SECRET = 'c2d7607f97ba968604f999cd6918aac6'

  def new
  end

  def clef_create
 		code = params[:code]
	  response = HTTParty.post("https://clef.io/api/authorize", { body: { code: code, app_id: CLEF_ID, app_secret: CLEF_SECRET} })
	  if response['success']
	    response = HTTParty.post("https://clef.io/api/info", { body: {access_token: response['access_token']} })
	    print response
	    @user = User.find_or_create_by_clef_id(email: response['info']['email'], clef_id: response['info']['id'])
	    redirect_to user_path(@user)
	  else
	  	render json: response.to_json
	  end
  end

  def show
  	@user = User.find(params[:id])
  end
end
