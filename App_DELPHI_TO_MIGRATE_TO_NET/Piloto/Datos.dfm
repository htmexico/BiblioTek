object dmDatos: TdmDatos
  OldCreateOrder = False
  Left = 232
  Top = 185
  Height = 418
  Width = 502
  object mDataBase1: TADOConnection
    Connected = True
    ConnectionString = 
      'Provider=MSDASQL.1;Persist Security Info=False;Data Source=BIBLI' +
      'OGES'
    LoginPrompt = False
    Left = 80
    Top = 24
  end
  object qryAnySQL: TADOQuery
    Connection = mDataBase1
    Parameters = <>
    Left = 248
    Top = 24
  end
end
