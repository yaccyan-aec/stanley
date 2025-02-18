REM fts2 のルートパス
set RootPath=c:\xampp\htdocs\fts_v5
REM fts2 データのルートパス
set DataPath=c:\data\fts2
REM ========================================================

set DATE=%date:/=%
echo %DATE%
REM set time2=%time: =0%   
REM set TIME=%time2::=%
REM echo %TIME%
REM set NAME=%DATE%%TIME:~0,6%
set NAME=%DATE:~7,1%
echo %NAME%


cd %RootPath%\app
REM %RootPath%\app\Console\cake SendNotice  >> %DataPath%\tmp\fts2_sendnotice_%NAME%.log -app %RootPath%\app
%RootPath%\app\Console\cake AddressbookConv -app %RootPath%\app 
