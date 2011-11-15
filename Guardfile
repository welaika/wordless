# A sample Guardfile
# More info at https://github.com/guard/guard#readme

guard 'haml', output: '.', input: 'src/views' do
  watch(/^.+(\.haml)/)
end

guard 'compass', configuration_file: 'compass.rb' do
  watch(/^.+\.s[ac]ss$/)
end
