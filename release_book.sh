cd ./doc/api/
gitbook build ./

rm -rf ./../../public/_book
cp -rf _book ../../public/