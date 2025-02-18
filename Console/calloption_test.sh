#!/bin/sh

# -- 絶対パスを取得
MyPath=$(cd $(dirname $0);pwd)

# -- 共通設定をインクルード
. ${MyPath}/getmybase

echo ${RootPath}

DATE=`date +%w`
PNUM=`ps aux | grep  ${MyPath}  | wc -l`
echo ${PNUM}

if [ ${PNUM} -le 4 ]; then

echo 'DO' $0 `date`
cd ${RootPath}/app/Console 
${RootPath}/app/Console/cake CallOptions $* -app ${RootPath}/app 

else
echo 'skip' $0 `date`
fi
