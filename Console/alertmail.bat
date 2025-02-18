REM fts2 のルートパス
set RootPath=c:\xampp\htdocs\fts_v5
REM fts2 データのルートパス
set DataPath=c:\data\fts2
REM ========================================================
REM コマンドプロンプトからはこんなカンジで呼ぶ。
REM c:\xampp\htdocs\fts_v5\app\Console>chkmail
REM 
REM こんなふうに展開される↓
REM c:\xampp\htdocs\fts_v5\app>c:\xampp\htdocs\fts_v5\app\Console\cake Errmails.ChkMail -app c:\xampp\htdocs\fts_v5\app
REM ========================================================

set DATE=%date:/=%
echo %DATE%
REM set time2=%time: =0%   
REM set TIME=%time2::=%
REM echo %TIME%
REM set NAME=%DATE%%TIME:~0,6%
set NAME=%DATE:~7,1%
echo %NAME%

REM ===================================================
REM デフォルトは　'1 month'
REM 変えたいときは、コマンドで
REM >alretmail "1 day"  （この場合は当日）
REM というように書く。（空白が入るときはリテラルでくくる）
REM 
REM ===================================================

cd %RootPath%\app
%RootPath%\app\Console\cake AlertMail -p %1 -app %RootPath%\app 
REM %RootPath%\app\Console\cake Alertmail -app %RootPath%\app 

