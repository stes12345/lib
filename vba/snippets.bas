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
