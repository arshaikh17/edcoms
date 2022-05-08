#!/bin/bash

CMS_ENV="prod";

# set the environment argument if specified.
if ! [ -z "$1" ]; then
    CMS_ENV=$1;
fi;

# install Sonata Admin dependencies
bower install ../../sonata-project/admin-bundle/bower.json

# change directory.
echo "Generating assets for $CMS_ENV environment";
cd "$(dirname "$0")/src/EdcomsCMS/TemplatesBundle/Resources/public";

# compile FE assets.
if [ $CMS_ENV != 'prod' ];
then
    npm run build:$CMS_ENV;
else 
    npm run build;
fi;
