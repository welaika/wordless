# -*- encoding: utf-8 -*-
# This file is used by sprockets_preprocessor.php to run coffescript processing

require 'rubygems'
require 'sprockets'
require 'coffee-script'

puts "/* Compiled by Sprockets using #{ExecJS::Runtimes.best_available.name} */"

# last argument is the file path
file_path = ARGV.pop

# any previous argument is appended to sprocket environment paths
environment = Sprockets::Environment.new
while ARGV.size != 0
  path = ARGV.shift
  environment.append_path(path)
end

file_name = File.basename(file_path)
file_name.gsub!(/\.coffee/, '')

puts environment[file_name].to_s
