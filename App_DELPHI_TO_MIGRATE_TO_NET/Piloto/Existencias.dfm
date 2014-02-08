object frmExistencias: TfrmExistencias
  Left = 192
  Top = 110
  Width = 764
  Height = 495
  Caption = 'Importar Titulos de c'#243'digo MARC'
  Color = clBtnFace
  Font.Charset = DEFAULT_CHARSET
  Font.Color = clWindowText
  Font.Height = -11
  Font.Name = 'MS Sans Serif'
  Font.Style = []
  FormStyle = fsMDIChild
  OldCreateOrder = False
  Position = poDefault
  Visible = True
  OnClose = FormClose
  OnCreate = FormCreate
  PixelsPerInch = 96
  TextHeight = 13
  object Label1: TLabel
    Left = 8
    Top = 16
    Width = 53
    Height = 13
    Caption = 'Terminador'
  end
  object Edit1: TEdit
    Left = 72
    Top = 8
    Width = 57
    Height = 21
    TabOrder = 0
    Text = '|'
  end
  object CheckBox1: TCheckBox
    Left = 168
    Top = 16
    Width = 97
    Height = 17
    Caption = 'Include CR/LF'
    Checked = True
    State = cbChecked
    TabOrder = 1
  end
  object Button1: TButton
    Left = 631
    Top = 96
    Width = 113
    Height = 41
    Caption = 'Pegar c'#243'digo MARC'
    TabOrder = 2
  end
  object Button2: TButton
    Left = 631
    Top = 409
    Width = 113
    Height = 41
    Caption = 'Importar'
    TabOrder = 3
  end
  object Button3: TButton
    Left = 631
    Top = 40
    Width = 113
    Height = 41
    Caption = 'Limpiar'
    TabOrder = 4
  end
end
