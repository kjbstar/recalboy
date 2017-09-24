# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

  config.vm.box = "debian/jessie64"

  config.vm.box_check_update = false

  config.vm.network "public_network"

  config.vm.provider "virtualbox" do |vb|
    vb.gui = false
    vb.memory = "512"
  end

  config.vm.provision "shell" do |p|
    p.path = "https://raw.githubusercontent.com/kjbstar/recalboy/master/provisionning.sh"
  end

  config.vm.synced_folder "c:/Recalboy", "/var/www/html/storage/app/public", type: "smb"

end