#!/bin/bash
SRC=~/bga/bga-caverna/ # with trailing slash
OLD=caverna
#NEW=cavernatisaac
NEW=caverna
TMP=/tmp/bgarewrite-$OLD
TMPNEW=/tmp/bgarewrite-$NEW/

# Sass
sass caverna.scss caverna.css

# Copy
rsync -r --delete --exclude=.git --exclude=misc --exclude=.sass-cache --exclude=node_modules/ --exclude=.vscode $SRC $TMP

# Rewrite contents
find $TMP -type f -not -name '*.png' -not -name '*.jpg' \
  -exec sed -i "" -e "s/$OLD/$NEW/g" {} \; 2> /dev/null

# Preserve modification time
TMPP="${TMP//\//\\/}"
find $TMP -type f \
  -exec bash -c "touch -r \${0/#$TMPP/$SRC} \$0" {} \;

# Rename
find $TMP -name "$OLD*" \
  -exec bash -c "mv \$0 \${0//$OLD/$NEW}" {} \;

mkdir -p $TMPNEW
cp -rp $TMP/* $TMPNEW

# Sync
rsync -vtr $TMPNEW ~/bga/studio/$NEW/
