@echo off & SETLOCAL EnableExtensions
endlocal & goto :EOF

::Task Scheduler - run task if it isn't running
TASKLIST | FINDSTR /I "%EXEName%"
IF ERRORLEVEL 1 GOTO :ReStartApp
GOTO :EOF

:ReStartApp
START "" "%EXEFullPath%"
GOTO :EOF


%SCRIPT_FOLDER%psexec \\<000.000.000.000> -u <userName> -p <pass> -s %SCRIPT_FOLDER%<scrept>.bat

if {%COMPUTERNAME%}=={%VARIABLE%}

FOR %%i IN ("%~f0") DO (set THIS_SCRIPT=%%~ni%%~xi)

echo %DATE% %TIME% 2>&1 >>%LOG_FILE%

%SystemRoot%\SysWOW64\TIMEOUT /T 180  >&2
    
%SystemRoot%\SysWOW64\net stop Spooler 2>&1 >>%LOG_FILE%

%SystemRoot%\SysWOW64\tasklist /FI "IMAGENAME eq %STOPPER_SCRIPT%" | find /i "%STOPPER_SCRIPT%" || (
	"%SCRIPT_FOLDER%%STOPPER_SCRIPT%" 2>&1 >>%LOG_FILE%
	echo %DATE% %TIME% %STOPPER_SCRIPT% restarting scheduled /%THIS_SCRIPT% 2>&1 >>%LOG_FILE%
	goto restarting
)

c:\windows\SysWOW64\schtasks.exe /run /tn startMyTask 2>&1 >>%LOG_FILE%

C:\Windows\SysWOW64\WindowsPowerShell\v1.0\PowerShell.exe -executionpolicy bypass -Command "& {. %SCRIPT_FOLDER%Connect-Mstsc1.ps1; Connect-Mstsc %REMOTE_COMP% <user> <pass> -<pass>}"


