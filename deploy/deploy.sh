#!/usr/bin/env bash

set -x

PLUGINSLUG=${PWD##*/}
CURRENTDIR=`pwd`
MAINFILE="wordless.php"
GITPATH="$CURRENTDIR/"

NEWVERSION1=`grep "^Stable tag" "$GITPATH/readme.txt" | awk -F' ' '{print $3}' | sed 's/[[:space:]]//g'`
echo "readme version: $NEWVERSION1"
NEWVERSION2=`grep "^Version" "$GITPATH/$MAINFILE" | awk -F' ' '{print $2}' | sed 's/[[:space:]]//g'`
echo "$MAINFILE version: $NEWVERSION2"
echo "Git last tag: $TRAVIS_TAG"

if [[ "$NEWVERSION1" != "$NEWVERSION2" ]]; then echo "Versions don't match. Exiting...."; exit 1; fi
if [[ "$NEWVERSION1" != "$TRAVIS_TAG" ]]; then echo "Versions don't match with last Git TAG. Exiting...."; exit 1; fi

# 1. Clone complete SVN repository to separate directory
svn co $SVN_REPOSITORY ../svn

rm -rf ../svn/trunk/*

# 2. Copy git repository contents to SNV trunk/ directory
cp -R ./* ../svn/trunk/

# 3. Switch to SVN repository
cd ../svn/trunk/

rm -rf ../assets

# 4. Move assets/ to SVN /assets/
mv ./assets/ ../assets/

# 5. Clean up unnecessary files
rm -rf .git/
rm -rf deploy/
rm .travis.yml

# 6. Go to SVN repository root
cd ../

# 7. Create SVN tag
svn cp trunk tags/$TRAVIS_TAG

# 8. Push SVN tag
svn ci  --message "Release $TRAVIS_TAG" \
        --username $SVN_USERNAME \
        --password $SVN_PASSWORD \
        --non-interactive
