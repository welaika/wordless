task :docs do
  docs_path = File.join(File.dirname(__FILE__), "docs")
  directory(docs_path)
  `doxygen #{File.join(docs_path, "Doxyfile")}`
  File.symlink(File.join(docs_path, "build", "html", "index.html"), File.join(docs_path, "build", "index.html"))
end
