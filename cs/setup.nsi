; -------------------------------
; Start

!define PRODUCT "Project"
!define VERSION "1.0.0"
Name "${PRODUCT} ${VERSION}"
!define ICONNAME "${PRODUCT}"
Icon "${ICONNAME}.ico"
 
!define MUI_PRODUCT "${PRODUCT}"
!define MUI_FILE "${PRODUCT}"
!define MUI_VERSION ""
!define MUI_BRANDINGTEXT "${PRODUCT}"

CRCCheck On

Unicode true
  
!define MUI_ICON "${ICONNAME}.ico"
!define MUI_UNICON "${ICONNAME}.ico" 
!define INSTALL_DIR "$PROGRAMFILES64\${MUI_PRODUCT}"

RequestExecutionLevel admin
  
;=== Include
!include "MUI2.nsh"
!include "FileFunc.nsh"
!insertmacro GetOptions
!include "LogicLib.nsh"
!include x64.nsh
!include Library.nsh
!include FileFunc.nsh
  
!include "DotNetChecker.nsh"
!include "${NSISDIR}\Contrib\Modern UI\System.nsh"
!include "EnvVarUpdate.nsh"
!include "InstallOptions.nsh"
  
!define /date MyTIMESTAMP "%Y-%m-%d-%H-%M-%S"

InstallDir "${INSTALL_DIR}"
OutFile "${PRODUCT}Installer${MyTIMESTAMP}.exe"
  
Function .onInit
	RMDir /r "${INSTALL_DIR}"
	CreateDirectory "${INSTALL_DIR}\"
    LogEx::Init /NOUNLOAD "${INSTALL_DIR}\InstallLog.txt"
    LogEx::Write /NOUNLOAD "Function .onInit : Log File Opened..."    
FunctionEnd

Section "!App Portable (required)"
  SetDetailsView show
SectionEnd

Section "Main"
	SetOutPath $INSTDIR
	;File "NDP472-DevPack-ENU.exe"
	File "NDP472-KB4054530-x86-x64-AllOS-ENU.exe"
	File "winscp-5.13.6-Automation\readme_automation.txt"
	File "winscp-5.13.6-Automation\license-dotnet.txt"
	File "winscp-5.13.6-Automation\license-winscp.txt"
	File "winscp-5.13.6-Automation\WinSCP.exe"
	File "winscp-5.13.6-Automation\WinSCPnet.dll"
	DetailPrint "Section Main"
	DotNetChecker::IsDotNet472Installed
	Pop $0
	
	${If} $0 == "false"
		ExecWait '"$INSTDIR\NDP472-KB4054530-x86-x64-AllOS-ENU.exe" >"$INSTDIR\ExecCmd.log"'
	${EndIf}
	Delete "$INSTDIR\NDP472-KB4054530-x86-x64-AllOS-ENU.exe"

	ExecWait '"$WINDIR\Microsoft.NET\Framework\v4.0.30319\RegAsm.exe"   "$INSTDIR\WinSCPnet.dll" /codebase /tlb:"$INSTDIR\WinSCPnet32.tlb" >"$INSTDIR\ExecCmd.log"'
	LogEx::AddFile "   >" "$INSTDIR\ExecCmd.log"
	${If} ${RunningX64}
		ExecWait '"$WINDIR\Microsoft.NET\Framework64\v4.0.30319\RegAsm.exe" "$INSTDIR\WinSCPnet.dll" /codebase /tlb:"$INSTDIR\WinSCPnet64.tlb" >"$INSTDIR\ExecCmd.log"'
	${EndIf}  
	LogEx::AddFile "   >" "$INSTDIR\ExecCmd.log"
	WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${MUI_PRODUCT}" "DisplayName"     "${MUI_PRODUCT}"
	WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${MUI_PRODUCT}" "UninstallString" "$\"$INSTDIR\uninstall.exe$\""
	${EnvVarUpdate} $0 "PATH" "A" "HKLM" $INSTDIR
	;https://nsis.sourceforge.io/Environmental_Variables:_append,_prepend,_and_remove_entries#Function_Code
SectionEnd


Section "-EndTime"
	GetTempFileName $R5
	GetFileTime $R5 $R6 $R7
	Delete $R5
	IntOp $R7 $R7 - $R9
	IntOp $R7 $R7 / 10000000
SectionEnd

Section "-CleanUp"
	WriteUninstaller "$INSTDIR\Uninstall.exe"
	LogEx::Close
SectionEnd

Section "uninstall"

	ExecWait '"$WINDIR\Microsoft.NET\Framework\v4.0.30319\RegAsm.exe"   /u WinSCPnet.dll'
	${If} ${RunningX64}
		ExecWait '"$WINDIR\Microsoft.NET\Framework64\v4.0.30319\RegAsm.exe" /u WinSCPnet.dll'
	${EndIf}  
	DeleteRegKey HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${MUI_PRODUCT}"
	${un.EnvVarUpdate} $0 "PATH" "R" "HKLM" $INSTDIR

	# Remove files
	delete $INSTDIR\*.*
 
	# Always delete uninstaller as the last action
	delete $INSTDIR\uninstall.exe
 
	# Try to remove the install directory - this will only happen if it is empty
	rmDir /r $INSTDIR
SectionEnd
