REM fts2 のルートパス
set RootPath=c:\xampp\htdocs\fts_stanley
REM fts2 データのルートパス
set DataPath=c:\data\fts_stanley
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
%RootPath%\app\Console\cake FileDelete >> %DataPath%/tmp/fts_clean_%DATE%.log -app %RootPath%\app 
