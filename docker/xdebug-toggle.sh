#!/bin/sh
# This script enables or disables Xdebug by renaming its configuration file.

# Exit immediately if a command exits with a non-zero status.
set -e

# Define the paths for the Xdebug config file
INI_FILE="/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
DISABLED_INI_FILE="${INI_FILE}.disabled"

# Check the first argument passed to the script
case "$1" in
  "on")
    # To enable, we rename the .disabled file back to .ini
    if [ -f "$DISABLED_INI_FILE" ]; then
      mv "$DISABLED_INI_FILE" "$INI_FILE"
      echo "Xdebug is now enabled."
    else
      echo "Xdebug is already enabled."
    fi
    ;;

  "off")
    # To disable, we rename the .ini file to .disabled
    if [ -f "$INI_FILE" ]; then
      mv "$INI_FILE" "$DISABLED_INI_FILE"
      echo "Xdebug is now disabled."
    else
      echo "Xdebug is already disabled."
    fi
    ;;

  *)
    # If the argument is not "on" or "off", show a usage message
    echo "Usage: xdebug [on|off]"
    exit 1
    ;;
esac

exit 0
