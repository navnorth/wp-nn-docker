#!/bin/bash
SUBMODULES="themes/wp-oet-theme themes/wp-oese-theme plugins/wp-stories-posts plugins/wp-gap-analysis plugins/wp-usahtmlmap plugins/wpdatatables plugins/wdt-compare-details"

# modify main git repo to use ssh instead of https (for auth)
cd /docker/oese/.git
sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' /docker/oese/.git/config
sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' /docker/oese/.gitmodules

# modify submods -- not all in the same locations
sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' /docker/oese/.git/modules/html/wp-content/themes/wp-oet-theme/config
sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' /docker/oese/.git/modules/html/wp-content/themes/wp-oese-theme/config
sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' modules/html/wp-content/plugins/wp-gap-analysis/config
sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' modules/html/wp-content/plugins/wp-content-mirror/config
sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' modules/html/wp-content/plugins/wp-stories-posts/config

sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' modules/docker/wp-content/plugins/wdt-compare-details/config
sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' modules/docker/wp-content/plugins/wpdatatables/config
sed -i 's/https\:\/\/github.com/git\@github\.com\:/g' modules/docker/wp-content/plugins/wp-usahtmlmap/config

for S in $SUBMODULES
do
  cd /docker/oese/docker/wp-content/$S


  git remote update 2>&1 >/dev/null
  echo "Checking submod $S for updates"
  UPSTREAM=${1:-'@{u}'}
  LOCAL=$(git rev-parse HEAD)
  REMOTE=$(git rev-parse "$UPSTREAM")
  BASE=$(git merge-base HEAD "$UPSTREAM")

  if [ $LOCAL = $REMOTE ]; then
    true
  elif [ $LOCAL = $BASE ]; then
    /docker/postmessage.php "Updating OESE submod $S"
    git pull
  fi
done
