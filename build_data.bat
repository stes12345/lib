@echo off & setlocal ENABLEEXTENSIONS enabledelayedexpansion
:: use with $(ProjectDir)\build_data.bat $(ProjectDir) $(RootNameSpace) to take build timestamp
set AppData=%1AppData.cs
call :GetTime h n s t
call :GetDate y m d
:: ${ProjectDir} $(RootNameSpace)
echo using System^;using System.Text.RegularExpressions^;namespace %2{public static class AppData{public static DateTime BuildAt =^>DateTime.ParseExact^(^"%y%-%m%-%d% %h%:%n%:%s%^"^,^"yyyy-MM-dd HH:mm:ss^",null);public static string GitSha1=^> @^" 2>&1> %AppData%
git rev-parse HEAD 2>&1>> %AppData%
echo ^".Trim^(^)^;public static string GitBranch =^> @^" 2>&1>> %AppData%
git rev-parse --abbrev-ref HEAD 2>&1>> %AppData%
echo ^".Trim^(^)^;public static DateTime LastCommit =^> new DateTime^(1970,1,1,0,0,0,0,DateTimeKind.Utc^).AddSeconds^(double.Parse^(Regex.Replace^(@^" 2>&1>> %AppData%
git show -s --format=%%ct 2>&1>> %AppData%
echo ^".Trim^(^), ^"^\^\s^+^", ^"^"^)^)^).ToLocalTime^(^)^;}} 2>&1>> %AppData%

endlocal&goto :EOF
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:GetDate yy mm dd
::
:: By:   Ritchie Lawrence, 2002-06-15. Version 1.0
::
:: Func: Loads local system date components into args 1 to 3.
::       For NT4/2000/XP/2003.
::
:: Args: %1 var to receive year, 4 digits (by ref)
::       %2 var to receive month, 2 digits, 01 to 12 (by ref)
::       %3 Var to receive day of month, 2 digits, 01 to 31 (by ref)
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
setlocal ENABLEEXTENSIONS
set t=2&if "%date%z" LSS "A" set t=1
for /f "skip=1 tokens=2-4 delims=(-)" %%a in ('echo/^|date') do (
  for /f "tokens=%t%-4 delims=.-/ " %%d in ('date/t') do (
    set %%a=%%d&set %%b=%%e&set %%c=%%f))
endlocal&set %1=%yy%&set %2=%mm%&set %3=%dd%&goto :EOF
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:GetTime hh nn ss tt
::
:: By:   Ritchie Lawrence, updated 2007-05-12. Version 1.3
::
:: Func: Loads local system time components into args 1 to 4.
::       For NT4/2000/XP/2003
::
:: Args: %1 Var to receive hours, 2 digits, 00 to 23 (by ref)
::       %2 Var to receive minutes, 2 digits, 00 to 59 (by ref)
::       %3 Var to receive seconds, 2 digits, 00 to 59 (by ref)
::       %4 Var to receive centiseconds, 2 digits, 00 to 99 (by ref)
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
setlocal ENABLEEXTENSIONS
for /f "tokens=5-8 delims=:,. " %%a in ('echo/^|time') do (
  set hh=%%a&set nn=%%b&set ss=%%c&set cs=%%d)
if 1%hh% LSS 20 set hh=0%hh%
endlocal&set %1=%hh%&set %2=%nn%&set %3=%ss%&set %4=%cs%&goto :EOF
