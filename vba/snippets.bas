Option Explicit

    ' https://winscp.net/eng/docs/library_vb#example
    ' %WINDIR%\Microsoft.NET\Framework\v4.0.30319\RegAsm.exe WinSCPnet.dll /codebase /tlb:WinSCPnet32.tlb
    ' %WINDIR%\Microsoft.NET\Framework64\v4.0.30319\RegAsm.exe WinSCPnet.dll /codebase /tlb:WinSCPnet64.tlb

' show deployed file
Dim ie As Object
Set ie = CreateObject("Internetexplorer.Application")
ie.Visible = True
ie.Navigate "<URL>/index.html"

Dim mySessionOptions As New SessionOptions
With mySessionOptions
	.Protocol = Protocol_Ftp
	.HostName = "<host>"
	.UserName = "<user>"
	.Password = "<pass>"
	.TlsHostCertificateFingerprint = "xx:xx:..."
	.FtpSecure = FtpSecure_Explicit
End With

mySession.ExecutablePath = Environ("LOCALAPPDATA") & "\<appName>\WinSCP.exe"
mySession.Open mySessionOptions
...
Set transferResult = mySession.PutFiles(Application.ActiveWorkbook.Path & "\index.html", "/test/", False, myTransferOptions)


With ActiveWorkbook.PublishObjects.Add(xlSourceRange, _
	Application.ActiveWorkbook.Path & "\index.html", _
	"<sheetName>", selectedStr, xlHtmlStatic, "", "")
	.Publish (True)
	.AutoRepublish = False
End With


Dim ie As Object
Set ie = CreateObject("Internetexplorer.Application")
ie.Visible = True
ie.Navigate "<url>/index.html"


'' =SUM(OFFSET(F3;0;0;1;COUNTA($F$2:$BDB$2)))
'' =SUM(OFFSET(<sheetName2>!F3;0;0;1;COUNTA(<sheetName2>!$F$2:$BDB$2)-1))

''https://stackoverflow.com/questions/22379546/vba-save-close-and-re-open-thisworkbook
Public bClose_ReOpen As Boolean
Public Const CMD_RESTART = "CMD /C PING 10.0.0.0 -n 1 -w 5000 >NUL & Excel "
Public Const CMD = "cmd.exe"
Private Sub Workbook_Open()
	Call killDosWindow
End Sub
Private Sub Workbook_Deactivate()
    If bClose_ReOpen Then
        Shell CMD_RESTART & Chr(34) & ThisWorkbook.FullName & Chr(34), vbNormalFocus
        If Application.Workbooks.Count = 1 Then
            Application.Quit
        End If
    End If
End Sub
Public Sub killDosWindow()
    Set objWMIService = GetObject("winmgmts:" & "{impersonationLevel=impersonate}!\\.\root\cimv2")
    Set colProcessList = objWMIService.ExecQuery("Select ProcessId from Win32_Process where Name='" & CMD & "' AND Commandline like '" & CMD_RESTART & "%'")
    For Each objOS In colProcessList
        objOS.Terminate
    Next objOS
End Sub

'' https://www.mrexcel.com/forum/excel-questions/386643-userform-always-top.html
Public Const SWP_NOMOVE = &H2
Public Const SWP_NOSIZE = &H1
Public Const HWND_TOP = 0
Public Const HWND_BOTTOM = 1
Public Const HWND_TOPMOST = -1
Public Const HWND_NOTOPMOST = -2
   
' Windows API calls to handle windows
#If VBA7 Then
    Public Declare PtrSafe Function FindWindow Lib "user32" Alias "FindWindowA" (ByVal lpClassName As String, ByVal lpWindowName As String) As Long
    Public Declare PtrSafe Function GetWindowLong Lib "user32" Alias "GetWindowLongA" (ByVal hwnd As Long, ByVal nIndex As Long) As Long
    Public Declare PtrSafe Function SetWindowLong Lib "user32" Alias "SetWindowLongA" (ByVal hwnd As Long, ByVal nIndex As Long, ByVal dwNewLong As Long) As Long
    Public Declare PtrSafe Function SetWindowPos Lib "user32" (ByVal hwnd As Long, ByVal hWndInsertAfter As Long, ByVal X As Long, ByVal Y As Long, ByVal cx As Long, ByVal cy As Long, ByVal uFlags As Long) As Long
#Else
    Public Declare Function FindWindow Lib "user32" Alias "FindWindowA" (ByVal lpClassName As String, ByVal lpWindowName As String) As Long
    Public Declare Function GetWindowLong Lib "user32" Alias "GetWindowLongA" (ByVal hwnd As Long, ByVal nIndex As Long) As Long
    Public Declare Function SetWindowLong Lib "user32" Alias "SetWindowLongA" (ByVal hwnd As Long, ByVal nIndex As Long, ByVal dwNewLong As Long) As Long
    Public Declare Function SetWindowPos Lib "user32" (ByVal hwnd As Long, ByVal hWndInsertAfter As Long, ByVal X As Long, ByVal Y As Long, ByVal cx As Long, ByVal cy As Long, ByVal uFlags As Long) As Long
#End If

Private Sub UserForm_Initialize()
	Const C_VBA6_USERFORM_CLASSNAME = "ProgressBar1"
    Dim ret As Long
    Dim formHWnd As Long
    formHWnd = FindWindow(C_VBA6_USERFORM_CLASSNAME, Me.Caption)
    ret = SetWindowPos(formHWnd, HWND_TOPMOST, 0, 0, 0, 0, SWP_NOMOVE Or SWP_NOSIZE)
End Sub

Public Sub subRemoveCloseButton(frm As Object)
    ' remove user form close "x" to prevent closing it
    Dim lngStyle As Long
    Dim lngHWnd As Long

    lngHWnd = FindWindow(vbNullString, frm.Caption)
    lngStyle = GetWindowLong(lngHWnd, mcGWL_STYLE)

    If lngStyle And mcWS_SYSMENU > 0 Then
        SetWindowLong lngHWnd, mcGWL_STYLE, (lngStyle And Not mcWS_SYSMENU)
    End If

End Sub

Public Function GetSheetByCodeName(codeName As String)
    ' open sheet by code name to not depend on visible name changes
    Dim sheets1 As Collection, sheetIndex As Integer, sheet As Worksheet
    For Each sheet In ActiveWorkbook.Worksheets
        If sheet.codeName = codeName Then
            Set GetSheetByCodeName = sheet
            Exit Function
        End If
    Next sheet
End Function
