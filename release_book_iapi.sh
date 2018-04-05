cd ./doc/iapi/
gitbook build ./
if [ ! -d "./../../public/iapi/" ];then
mkdir ./../../public/iapi/
fi
rm -rf ./../../public/iapi/_book
cp -rf _book ../../public/iapi/