#!/bin/bash

usage="Usage: deploy -gomfcvq | --all [--prune] [-h|--help]";

if [ -z $1 ]; then
    default=true
fi

die() { echo "$*" >&2; exit 2; }  # complain to STDERR and exit with error
needs_arg() { if [ -z "$OPTARG" ]; then die "No argument for --$OPT option"; fi; }

while getopts ":gom:cvqh-:" OPT; do
  # support long options: https://stackoverflow.com/a/28466267/519360
  if [ "$OPT" = "-" ]; then   # long option: reformulate OPT and OPTARG
    OPT="${OPTARG%%=*}"       # extract long option name
    OPTARG="${OPTARG#$OPT}"   # extract long option argument (may be empty)
    OPTARG="${OPTARG#=}"      # if long option argument, remove assigning `=`
  fi
  case "$OPT" in
    g | git )
      git=true ;;
    prune )
      git_prune=true ;;
    o | optimize )
      optimize=true ;;
    m | migrate )
      needs_arg
      migrate=true
      if [ "$OPTARG" != "all" ]; then
        migrate_file="database/storage/$OPTARG"
        if [ -e $migrate_file ]; then
          migrate_opts="--path=$migrate_file"
        fi
        if [ -e $OPTARG ]; then
          migrate_opts="--path=$OPTARG"
        fi
      fi
      ;;
    f | force )
      migrate_opts="--force $migrate_opts"
      ;;
    c | config )
      config=true ;;
    v | views )
      views=true ;;
    q | queue )
      queue=true ;;
    all )
      git=true
      optimize=true
      migrate=true
      queue=true
      ;;
    h | help )
      echo "Usage:"
      echo "  deploy [options] [-h|--help]"
      echo ""
      echo "Options:"
      echo "  -g, --git                 Run 'git pull' and 'rm bootstrap/cache/*.php'"
      echo "      --prune               Runs 'git fetch -p' and then 'git branch -d' on branches that are gone"
      echo "  -m, --migrate[=FILE|all]  Run 'php artisan migrate', with filename or all"
      echo "  -f, --force               Add '--force' to migrate options"
      echo "  -c, --config              Run 'php artisan config:cache'"
      echo "  -o, --optimize            Run 'php artisan optimize:clear && php artisan optimize'"
      echo "  -v, --views               Run 'php artisan view:clear'"
      echo "  -q, --queue               Run 'php artisan queue:restart'"
      echo "      --all                 Run git, optimize"
      echo "  -h, --help                Display this help screen"
      exit 0 ;;
    ??* )
      die "Illegal option --$OPT" ;;  # bad long option
    ? )
      echo $usage
      die "Missing argument or illegal option"
      exit 2 ;;  # bad short option (error reported via getopts)
  esac
done
shift $((OPTIND-1)) # remove parsed options and args from $@ list

if [ $default ]; then
  git=true
  optimize=true
fi
if [ $git_prune ]; then
  echo "Running git fetch -p..."
  git fetch -p
  branches=$(git branch -vv | awk '/^ .*gone/{print $1}')
  if [ $(echo $branches | wc -w) -gt 0 ]; then
    echo "Running git branch -d..."
    git branch -d $branches
  else
    echo "No branches to delete"
  fi
  echo "Current branches..."
  git branch -vv
fi
if [ $git ]; then
  echo "Running git pull..."
  git pull || exit 1
fi
if [ $migrate ]; then
  echo "Running artisan migrate..."
  php artisan migrate $migrate_opts || exit 1
fi
if [ $config ]; then
  echo "Running artisan config:cache..."
  php artisan config:cache || exit 1
fi
if [ $views ]; then
  echo "Running artisan view:clear..."
  php artisan view:clear || exit 1
fi
if [ $optimize ]; then
  echo "Running artisan optimize..."
  php artisan optimize:clear || exit 1
  php artisan optimize || exit 1
fi
if [ $queue ]; then
  echo "Running artisan queue:restart..."
  php artisan queue:restart || exit 1
fi
