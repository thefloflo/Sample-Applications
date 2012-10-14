class CreateUsers < ActiveRecord::Migration
  def change
    create_table :users do |t|
      t.string :email
      t.string :clef_id

      t.timestamps
    end
  end
end
