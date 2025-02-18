REM fts2 のルートパス
SET RootPath=c:\xampp\htdocs\fts_v5
REM fts2 データのルートパス
SET DataPath=c:\data\fts2
REM ========================================================
REM コマンドプロンプトからはこんなカンジで呼ぶ。
REM c:\xampp\htdocs\fts_v5\app\Console>chkmail
REM 
REM こんなふうに展開される↓
REM c:\xampp\htdocs\fts_v5\app>c:\xampp\htdocs\fts_v5\app\Console\cake Errmails.ChkMail -app c:\xampp\htdocs\fts_v5\app
REM ========================================================

SET DATE=%date:/=%
echo %DATE%
REM set time2=%time: =0%   
REM set TIME=%time2::=%
REM echo %TIME%
REM set NAME=%DATE%%TIME:~0,6%
SET NAME=%DATE:~7,1%
echo %NAME%


cd %RootPath%\app
%RootPath%\app\Console\cake Errmails.ChkMail -app %RootPath%\app 
REM %RootPath%\app\Console\cake Errmails.ChkMail > %DataPath%/tmp/fts_clean_%DATE%.log -app %RootPath%\app 
