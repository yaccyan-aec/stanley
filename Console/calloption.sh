#!/bin/sh
# -- 絶対パスを取得
MyPath=$(cd $(dirname $0);pwd)
# -- 共通設定をインクルード
. ${MyPath}/getmybase
DATE=`date +%w`
PNUM=`ps aux | grep  $0  | wc -l`
echo ${PNUM}
if [ ${PNUM} -lt 6 ]; then
echo 'DO' $0 `date`
cd ${RootPath}/app/Console
#${RootPath}/app/Console/cake CallOptions $* -app ${RootPath}/app >> ${LogPath}/fts_opt_shell_${DATE}.log
php ${RootPath}/app/Console/cake.php CallOptions $*  >> ${LogPath}/fts_opt_shell_${DATE}.log -app ${RootPath}/app
else
echo 'skip' $0 `date`
fi