#!/bin/bash

# Create the docs folder if doesn't exists
[[ -d ./docs ]] || mkdir docs
# Run doxygen
doxygen
# Create the link to docs / to simplify access to docs )
#rm docs/index.html
[[ -e docs/index.html ]] || ln -s html/index.html docs/index.html
#ln -s html/index.html docs/index.html
