Const ROW_FIELDS = 1
Const COLUMNS_MAX = 20
Const ROWS_MAX = 1000
Sub convert()
    ' get fields
    Dim wb As Workbook
    Dim w
    For Each w In Workbooks
        If w.Name = "my.xls" Then
            Set wb = w
            Exit For
        End If
    Next
    If wb Is Nothing Then
        MsgBox ("workbook not found")
        Exit Sub
    End If
    Dim ws As Worksheet
    For Each w In wb.Worksheets
        If w.Name = "insert" Then
            Set ws = w
            Exit For
        End If
    Next
    If ws Is Nothing Then
        MsgBox ("worksheet not found")
        Exit Sub
    End If
    Dim sFields As String
    sFields = ""
    Dim nColumn As Integer
    nColumn = 1
    While Trim(ws.Cells(ROW_FIELDS, nColumn).Text) <> "" And nColumn < COLUMNS_MAX
        If sFields <> "" Then sFields = sFields & ","
        sFields = sFields & Trim(ws.Cells(ROW_FIELDS, nColumn).Text)
        nColumn = nColumn + 1
    Wend
    ' open output file
    Open wb.Path & "\insert.sql" For Output As #1
    ' save data
    Dim nColumnMax As Integer
    nColumnMax = nColumn - 1
    Dim nRow As Integer
    nRow = ROW_FIELDS + 1
    Dim sFieldName As String
    Dim sValues As String
    Dim sValue As String
    Dim sUpdates As String
    While Trim(ws.Cells(nRow, 1).Text) <> "" And nRow < ROWS_MAX
        sValues = ""
        sUpdates = ""
        For nColumn = 1 To nColumnMax
            sFieldName = Trim(ws.Cells(ROW_FIELDS, nColumn).Text)
            If sValues <> "" Then sValues = sValues & ","
            'sValue = quoteMySQL(Unicode2cp1251(Trim(ws.Cells(nRow, nColumn).Text)))
            sValue = quoteMySQL(cp1251orUnicode2utf8(Trim(ws.Cells(nRow, nColumn).Text)))
            If sFieldName = "code" Then
                sValue = code2latin(sValue)
            ElseIf sFieldName = "level_idlevel" Then
                If sValue = "Í" Then
                    sValue = 3
                ElseIf sValue = "Î" Then
                    sValue = 4
                Else
                    sValue = 5
                End If
            End If
            sValues = sValues & sValue
            If sFieldName <> "code" Then
                If sUpdates <> "" Then sUpdates = sUpdates & ","
                sUpdates = sUpdates & sFieldName & "=" & sValue
            End If
        Next
        Print #1, "insert into crl_users (" & sFields & ") value (" & sValues & ")" & _
        " ON DUPLICATE KEY UPDATE " & sUpdates & ";"
        nRow = nRow + 1
    Wend
    Close #1
End Sub
Function code2latin(vCode As Variant)
    Dim newCode As String, nChar As String
    If IsNull(vCode) Or vCode = "" Then code2latin = vCode: Exit Function
    newCode = ""
    For nPos = 1 To Len(vCode)
        nChar = Mid(vCode, nPos, 1)
        If nChar = "Ñ" Then ' S
            nChar = "C"
        ElseIf nChar = "Å" Then ' E
            nChar = "E"
        ElseIf nChar = "Â" Then ' B
            nChar = "B"
        ElseIf nChar = "Ê" Then ' K
            nChar = "K"
        End If
        newCode = newCode & nChar
    Next
    code2latin = newCode
End Function
Function quoteMySQL(vValue As Variant)
    quoteMySQL = "'" & Replace(vValue, "'", "\'") & "'"
End Function
Private Function cp1251orUnicode2utf8(sValue As Variant) As Variant
    Dim sNew As String, nPos As Long, nChar As Long
    If IsNull(sValue) Or sValue = "" Then cp1251orUnicode2utf8 = sValue: Exit Function
    sNew = ""
    For nPos = 1 To Len(sValue)
        nChar = AscW(Mid(sValue, nPos, 1))
        If nChar > &HBF And nChar < &H100 Then
            If nChar < &HF0 Then
                sNew = sNew & Chr(&HD0) & Chr(nChar - &H30) ' C0->410->D090
            Else
                sNew = sNew & Chr(&HD1) & Chr(nChar - &H70) ' FF->44F->D18F
            End If
        ElseIf nChar > &H40F And nChar < &H450 Then
            If nChar < &H440 Then
                sNew = sNew & Chr(&HD0) & Chr(nChar - &H380)
            Else
                sNew = sNew & Chr(&HD1) & Chr(nChar - &H3C0)
            End If
        ElseIf nChar = &H406 Then ' I
            sNew = sNew & Chr(&HD0) & Chr(&H86)
        ElseIf nChar = &HA0 Then ' " -> 20
            sNew = sNew & " "
        ElseIf nChar = &H201C Then ' " -> E2 80 9C
            sNew = sNew & Chr(&HE2) & Chr(&H80) & Chr(&H9C)
        ElseIf nChar = &H201D Then ' " -> E2 80 9D
            sNew = sNew & Chr(&HE2) & Chr(&H80) & Chr(&H9D)
        ElseIf nChar = &H2116 Then ' No -> E2 84 96
            sNew = sNew & Chr(&HE2) & Chr(&H84) & Chr(&H96)
        ElseIf nChar = &H2013 Then ' - -> E2 80 93
            sNew = sNew & Chr(&HE2) & Chr(&H80) & Chr(&H93)
        ElseIf nChar = &H201E Then ' -> E2 80 9E
            sNew = sNew & Chr(&HE2) & Chr(&H80) & Chr(&H9E)
        ElseIf nChar > &H7F Then
            MsgBox (Hex(nChar))
        Else
            sNew = sNew & ChrW(nChar)
        End If
    Next
    cp1251orUnicode2utf8 = sNew
End Function
Private Function Unicode2cp1251(sValue As Variant) As Variant ' 410->C0
    Dim sNew As String, nPos As Long, nChar As Long
    If IsNull(sValue) Or sValue = "" Then Unicode2cp1251 = sValue: Exit Function
    sNew = ""
    For nPos = 1 To Len(sValue)
        nChar = AscW(Mid(sValue, nPos, 1))
        If nChar > &H40F And nChar < &H450 Then
            sNew = sNew & Chr(nChar - &H350)
        ElseIf nChar = &H406 Then ' I
            sNew = sNew & "I"
        ElseIf nChar = &HA0 Then ' <sp> -> 20
            sNew = sNew & " "
        ElseIf nChar > &H201B And nChar < &H201F Then ' " -> E2 80 9C
            sNew = sNew & """"
        ElseIf nChar > &H2017 And nChar < &H201C Then ' ' -> E2 80 9D
            sNew = sNew & "'"
        ElseIf nChar = &H2116 Then ' No -> E2 84 96
            sNew = sNew & "N"
        ElseIf nChar = &H2013 Then ' - -> E2 80 93
            sNew = sNew & "-"
        ElseIf nChar > &H7F Then
            MsgBox (Hex(nChar))
        Else
            sNew = sNew & ChrW(nChar)
        End If
    Next
    Unicode2cp1251 = sNew
End Function
