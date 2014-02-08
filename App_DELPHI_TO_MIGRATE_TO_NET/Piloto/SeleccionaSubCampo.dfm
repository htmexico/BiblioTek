object frmSeleccionaSubCampo: TfrmSeleccionaSubCampo
  Left = 281
  Top = 175
  BorderStyle = bsDialog
  Caption = 'Agregar Subcampos'
  ClientHeight = 433
  ClientWidth = 450
  Color = clBtnFace
  Font.Charset = DEFAULT_CHARSET
  Font.Color = clWindowText
  Font.Height = -11
  Font.Name = 'MS Sans Serif'
  Font.Style = []
  OldCreateOrder = False
  OnCreate = FormCreate
  OnDestroy = FormDestroy
  PixelsPerInch = 96
  TextHeight = 13
  object Label1: TLabel
    Left = 16
    Top = 16
    Width = 39
    Height = 13
    Caption = 'Label1'
    Font.Charset = DEFAULT_CHARSET
    Font.Color = clWindowText
    Font.Height = -12
    Font.Name = 'MS Sans Serif'
    Font.Style = [fsBold]
    ParentFont = False
  end
  object chkLstCodificacionBasica: TCheckListBox
    Left = 8
    Top = 40
    Width = 433
    Height = 337
    Font.Charset = DEFAULT_CHARSET
    Font.Color = clWindowText
    Font.Height = -11
    Font.Name = 'Courier New'
    Font.Style = []
    ItemHeight = 14
    ParentFont = False
    TabOrder = 0
  end
  object Encarta1: TEncarta
    Left = 72
    Top = 388
    Width = 153
    Height = 33
    Caption = '&Agregar Sub Campos'
    TabOrder = 1
    OnClick = Encarta1Click
    XPStyle = True
    Kind = bkContinue
  end
  object Encarta2: TEncarta
    Left = 244
    Top = 388
    Width = 157
    Height = 33
    Caption = 'Salir de esta ventana'
    TabOrder = 2
    XPStyle = True
    Kind = bkCancel3D
  end
end
