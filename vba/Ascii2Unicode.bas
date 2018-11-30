' Word Macro Unicode-Cyr -> UpperCase translaction
Public Sub CyrUnicodeUpper()
    Dim sSection As Section, t As TextFrame, s As Shape
    For Each sSection In ActiveDocument.Sections
        Selection.WholeStory
        Call CyrUnicodeUpperW
    Next sSection
    For Each s In ActiveDocument.Shapes
        With s.TextFrame
            If .HasText Then
                s.Select
                Call CyrUnicodeUpperW
            End If
        End With
    Next
End Sub
Private Sub CyrUnicodeUpperW()
    If (Selection.End = Selection.Start) Then
        Selection.HomeKey Unit:=wdStory
        Selection.EndKey Unit:=wdStory, Extend:=wdExtend
    End If
    selEnd = Selection.End
    Selection.MoveLeft
    startPos = Selection.Start
    currPos = startPos
    While (currPos < selEnd)
        Selection.MoveRight Unit:=wdCharacter, Count:=1, Extend:=wdExtend
        Code = AscW(Selection.Text)
        If (Code > &H42F) And (Code < &H450) Then
            Selection.TypeText ChrW(Code - &H20)
        Else
            Selection.MoveRight
        End If
        currPos = currPos + 1
    Wend
End Sub
' Word Macro MIK -> cp1251 translaction
Public Sub Dos2cp1251Word()
    Dim sSection As Section, t As TextFrame, s As Shape
    For Each sSection In ActiveDocument.Sections
        Selection.WholeStory
        Call Dos2Unicode2
    Next sSection
    For Each s In ActiveDocument.Shapes
        With s.TextFrame
            If .HasText Then
                s.Select
                Call Dos2cp1251
            End If
        End With
    Next
End Sub
Private Sub Dos2cp1251()
    If (Selection.End = Selection.Start) Then
        Selection.HomeKey Unit:=wdStory
        Selection.EndKey Unit:=wdStory, Extend:=wdExtend
    End If
    selEnd = Selection.End
    Selection.MoveLeft
    startPos = Selection.Start
    currPos = startPos
    While (currPos < selEnd)
           
        Selection.MoveRight Unit:=wdCharacter, Count:=1, Extend:=wdExtend
        Code = AscB(Selection.Text)
        If (Code > &H7F) And (Code < &HC0) Then
            Selection.TypeText ChrB(Code + &H40)
        Else
            Selection.MoveRight
        End If
        currPos = currPos + 1
    Wend
End Sub

' Word Macro DOS-Cyr -> Unicode translaction
Public Sub Dos2UnicodeWord()
    Dim sSection As Section, t As TextFrame, s As Shape
    For Each sSection In ActiveDocument.Sections
        Selection.WholeStory
        Call Dos2Unicode
    Next sSection
    For Each s In ActiveDocument.Shapes
        With s.TextFrame
            If .HasText Then
                s.Select
                Call Dos2Unicode
            End If
        End With
    Next
End Sub
Private Sub Dos2Unicode()
    If (Selection.End = Selection.Start) Then
        Selection.HomeKey Unit:=wdStory
        Selection.EndKey Unit:=wdStory, Extend:=wdExtend
    End If
    selEnd = Selection.End
    Selection.MoveLeft
    startPos = Selection.Start
    currPos = startPos
    While (currPos < selEnd)
           
        Selection.MoveRight Unit:=wdCharacter, Count:=1, Extend:=wdExtend
        Code = AscW(Selection.Text)
        If (Code > &H7F) And (Code < &HC0) Then
            Selection.TypeText ChrW(Code + &H390)
        Else
            Selection.MoveRight
        End If
        currPos = currPos + 1
    Wend
End Sub
'--------------------------------------------------------------------------------------
' Word Macro ASCII -> Unicode translaction
Public Sub Ascii2UnicodeWord()
    Dim sSection As Section, t As TextFrame, s As Shape
    For Each sSection In ActiveDocument.Sections
        Selection.WholeStory
        Call Ansi2Unicode
    Next sSection
    For Each s In ActiveDocument.Shapes
        With s.TextFrame
            If .HasText Then
                s.Select
                Call Ansi2Unicode
            End If
        End With
    Next
End Sub
Private Sub Ansi2Unicode()
    If (Selection.End = Selection.Start) Then
        Selection.HomeKey Unit:=wdStory
        Selection.EndKey Unit:=wdStory, Extend:=wdExtend
    End If
    selEnd = Selection.End
    Selection.MoveLeft
    startPos = Selection.Start
    currPos = startPos
    While (currPos < selEnd)
           
        Selection.MoveRight Unit:=wdCharacter, Count:=1, Extend:=wdExtend
        Code = AscW(Selection.Text)
        If (Code >= 192) And (Code <= 255) Then
            Selection.TypeText ChrW(Code + 848)
        Else
            Selection.MoveRight
        End If
        currPos = currPos + 1
    Wend
End Sub
'--------------------------------------------------------------------------------------
' Excel Macro ASCII -> Unicode translaction
Sub Ascii2UnicodeExcel()
    Dim WCount As Long, i As Long, j As Long, k As Long, r As Range
    WCount = Worksheets.Count
    For i = 1 To WCount
        If Worksheets(WCount - i + 1).Visible Then
            Worksheets(WCount - i + 1).Select
            RCount = ActiveCell.SpecialCells(xlLastCell).Row
            CCount = ActiveCell.SpecialCells(xlLastCell).Column
            For j = 1 To RCount
                For k = 1 To CCount
                    If InStr(Worksheets(WCount - i + 1).Cells(j, k).Formula, "=") <> 1 Then _
                        Worksheets(WCount - i + 1).Cells(j, k).Value = _
                        Ascii2Unicode(Worksheets(WCount - i + 1).Cells(j, k).Value)
                Next k
            Next j
        End If
    Next i
End Sub
' Next example replace formulas with current values
' If you use next line in the cycle before you will remove all formulas in the current workbook
' Worksheets(WCount - i + 1).Cells(j, k)= Worksheets(WCount - i + 1).Cells(j, k).Value
' <End of example>
Private Function Ascii2Unicode(c As Variant) As Variant
    Dim sNew As String, nPos As Long, nChar As Long
    If IsNull(c) Or c = "" Then Ascii2Unicode = c: Exit Function
    sNew = ""
    For nPos = 1 To Len(c)
        nChar = AscW(Mid(c, nPos, 1))
        If nChar > &HBF And nChar < &H100 Then
            nChar = nChar + &H350
        End If
        sNew = sNew & ChrW(nChar)
    Next
    Ascii2Unicode = sNew
End Function

'--------------------------------------------------------------------------------------
' Word Macro Unicode -> ASCII translaction
Public Sub Unicode2AnsiWord()
    Dim sSection As Section, t As TextFrame, s As Shape
    For Each sSection In ActiveDocument.Sections
        Selection.WholeStory
        Call Unicode2Ansi
    Next sSection
    For Each s In ActiveDocument.Shapes
        With s.TextFrame
            If .HasText Then
                s.Select
                Call Unicode2Ansi
            End If
        End With
    Next
End Sub
Private Sub Unicode2Ansi()
    If (Selection.End = Selection.Start) Then
        Selection.HomeKey Unit:=wdStory
        Selection.EndKey Unit:=wdStory, Extend:=wdExtend
    End If
    selEnd = Selection.End
    Selection.MoveLeft
    startPos = Selection.Start
    currPos = startPos
    While (currPos < selEnd)
           
        Selection.MoveRight Unit:=wdCharacter, Count:=1, Extend:=wdExtend
        Code = AscW(Selection.Text)
        If (Code >= &H410) And (Code <= &H45F) Then
            Selection.TypeText ChrW(Code - 848)
        Else
            Selection.MoveRight
        End If
        currPos = currPos + 1
    Wend
End Sub

byte [] buffer = new byte [1024]; 
byte [] outbuffer = new byte[buffer.length]; 
int nBytesRead = 0; 

while (nBytesRead != -1 ){
nBytesRead = fis.read(buffer); 

for (int i=0; i<nBytesRead; i++){
byte symbol = buffer[i];
if(0x80 <= symbol || symbol <= 0xBF){
symbol += 0x40 ; 
}
outbuffer[i] = symbol;
}
'--------------------------------------------------------------------------------------
' Word Macro Unicode -> ASCII translaction
Public Sub Unicode2AsciiWord()
    Dim sSection As Section, t As TextFrame, s As Shape
    For Each sSection In ActiveDocument.Sections
        Selection.WholeStory
        Call Unicode2Ascii
    Next sSection
    For Each s In ActiveDocument.Shapes
        With s.TextFrame
            If .HasText Then
                s.Select
                Call Unicode2Ascii
            End If
        End With
    Next
End Sub
Private Sub Unicode2Ascii()
    If (Selection.End = Selection.Start) Then
        Selection.HomeKey Unit:=wdStory
        Selection.EndKey Unit:=wdStory, Extend:=wdExtend
    End If
    selEnd = Selection.End
    Selection.MoveLeft
    startPos = Selection.Start
    currPos = startPos
    While (currPos < selEnd)
           
        Selection.MoveRight Unit:=wdCharacter, Count:=1, Extend:=wdExtend
        Code = AscW(Selection.Text)
        If (Code > &H42F) And (Code < &H450) Then
            Selection.TypeText ChrW(Code - &H20)
        Else
            Selection.MoveRight
        End If
        currPos = currPos + 1
    Wend
End Sub
