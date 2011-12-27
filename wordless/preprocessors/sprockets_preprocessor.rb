# -*- encoding: utf-8 -*-
# This file is used by sprockets_preprocessor.php to run coffescript processing

require 'rubygems'
require 'thor'

class WordlessCLI < Thor

  desc "compile ASSET_PATH", "Compile an asset using Sprockets"
  method_option :compress, :aliases => "-c", :default => false, :type => :boolean, :desc => "Compress the output using YUI Compressor"
  method_option :munge, :aliases => "-m", :default => false, :type => :boolean, :desc => "When compressing, shorten local variable names"
  method_option :paths, :aliases => "-p", :default => [], :type => :array, :desc => "Appends additional paths to Sprockets environment"
  method_option :output, :aliases => "-o", :type => :string, :default => false, :desc => "Writes the result to the specified file"

  def compile(file_path)
    require 'sprockets'
    require 'coffee-script'

    output = "/* Compiled by Sprockets! using #{ExecJS::Runtimes.best_available.name} */\n"

    environment = Sprockets::Environment.new
    options[:paths].each { |path| environment.append_path(path) }

    file_name = File.basename(file_path)
    file_name.gsub!(/\.coffee/, '')

    output += environment[file_name].to_s

    if options[:compress]
      require "yui/compressor"
      compressor = YUI::JavaScriptCompressor.new(:munge => options[:munge])
      output = compressor.compress(output)
    end

    if options[:output]
      File.open(options[:output], 'w') {|f| f.write(output) }
    else
      puts output
    end
  end

end

WordlessCLI.start

