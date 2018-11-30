Public Function Ascii2Unicode(c As Variant) As Variant
    Dim sNew As String, nPos As Long, nChar As Long
    If IsNull(c) Or c = ""  Then Ascii2Unicode = c: Exit Function
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
