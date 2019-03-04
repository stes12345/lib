Attribute VB_Name = "utf8_2Unicode"
Public Sub utf8_2UnicodeExcel()
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
                        utf8_2Unicode(Worksheets(WCount - i + 1).Cells(j, k).Value)
                Next k
            Next j
        End If
    Next i
End Sub
' preg_replace('/([\xD0\xD1])([\x80-\xBF])/e','(ord("$1")==0xD0)?chr(4).chr(ord("$2")-0x80):chr(4).chr(ord("$2")-0x40)',$s);
Private Function utf8_2Unicode(c As Variant) As Variant
    Dim sNew As String, nPos As Long, nChar As Long, nChar2 As Long
    If IsNull(c) Or c = "" Then utf8_2Unicode = c: Exit Function
    sNew = ""
    For nPos = 1 To Len(c)
        nChar = AscW(Mid(c, nPos, 1))
        If nChar >= &H410 Or nChar <= &H450 Then
            nChar = nChar - &H350
        End If
        sNew = sNew & ChrW(nChar)
    Next nPos
    utf8_2Unicode = sNew
End Function


