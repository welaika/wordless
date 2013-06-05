require 'tempfile'

def colorize(text, color_code)
  "#{color_code}#{text}\033[0m"
end

def red(text)
  colorize(text, "\033[31m")
end

def green(text)
  colorize(text, "\033[32m")
end

desc "Create the project documentation using Doxygen."
task :docs do
  docs_path = File.join(File.dirname(__FILE__), "docs")
  directory(docs_path)
  `doxygen #{File.join(docs_path, "Doxyfile")}`
  shortcut_path = File.join(docs_path, "build", "index.html")
  original_path = File.join(docs_path, "build", "html", "index.html")
  File.symlink(original_path, shortcut_path) unless File.exists?(shortcut_path)
end

desc "Run all SimpleTest test suite"
task :tests do
  tests_path = File.join(File.dirname(__FILE__), "tests")
  Dir.chdir(tests_path) do
    temp_file = Tempfile.open('testresults')
    temp_path = temp_file.path
    temp_file.close!
    sh("php all_tests.php 1>#{temp_path} 2>&1") do |ok, res|
      output = File.read(temp_path)
      puts output.gsub(/^(OK\n.*)/, green("\\1")).
        gsub(/^(FAILURES!!!\n.*)/, red("\\1"))
      ok or fail
    end
  end
end

