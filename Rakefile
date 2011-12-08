desc "Create the project documentation using Doxygen."
task :docs do
  docs_path = File.join(File.dirname(__FILE__), "docs")
  directory(docs_path)
  `doxygen #{File.join(docs_path, "Doxyfile")}`
  shortcut_path = File.join(docs_path, "build", "index.html")
  original_path = File.join(docs_path, "build", "html", "index.html")
  File.symlink(original_path, shortcut_path) unless File.exists?(shortcut_path)
end
