object frmAdminCuentas: TfrmAdminCuentas
  Left = 192
  Top = 110
  Width = 696
  Height = 451
  Caption = 'Administraci'#243'n de Cuentas'
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
    Left = 16
    Top = 16
    Width = 83
    Height = 13
    Caption = 'Cuentas Actuales'
  end
  object btnCancel: TEncarta
    Left = 575
    Top = 382
    Width = 81
    Height = 25
    Caption = 'Salir'
    TabOrder = 0
    OnClick = btnCancelClick
    XPStyle = True
    Kind = bkCancel3D
  end
  object btnSave: TEncarta
    Left = 384
    Top = 22
    Width = 153
    Height = 25
    Caption = 'Crear Nueva Cuenta'
    TabOrder = 1
    XPStyle = True
    Kind = bkSave3D
  end
  object Encarta1: TEncarta
    Left = 552
    Top = 22
    Width = 121
    Height = 25
    Caption = 'Editar Cuenta'
    TabOrder = 2
    XPStyle = True
    Kind = bkSave3D
  end
  object xpPageControl1: TxpPageControl
    Left = 16
    Top = 48
    Width = 657
    Height = 321
    ActivePage = xpTabSheet1
    ParentShowHint = False
    ShowHint = True
    Style = pcsXP
    TabHeight = 25
    TabOrder = 3
    TabPosition = tpTop
    BorderColor = clSilver
    TabTextAlignment = taCenter
    object xpTabSheet1: TxpTabSheet
      Caption = 'Bibliotecas'
      Color = clWhite
      BGStyle = bgsNone
      GradientStartColor = clWhite
      GradientEndColor = clSilver
      GradientFillDir = fdTopToBottom
      object ExDBGrid1: TExDBGrid
        Left = 14
        Top = 12
        Width = 627
        Height = 269
        DataSource = DataSource1
        TabOrder = 0
        TitleFont.Charset = DEFAULT_CHARSET
        TitleFont.Color = clWindowText
        TitleFont.Height = -11
        TitleFont.Name = 'MS Sans Serif'
        TitleFont.Style = []
        ScrollBars = ssHorizontal
        EditColor = clWindow
        FixedRowHeight = 17
        DefaultRowHeight = 17
        RowColor1 = 12255087
        RowColor2 = clWindow
        HighlightColor = clNavy
        ImageHighlightColor = clWindow
        HighlightFontColor = clWhite
        HotTrackColor = clNavy
        LockedCols = 0
        LockedFont.Charset = DEFAULT_CHARSET
        LockedFont.Color = clWindowText
        LockedFont.Height = -11
        LockedFont.Name = 'MS Sans Serif'
        LockedFont.Style = []
        LockedColor = clGray
        ExMenuOptions = [exAutoSize, exAutoWidth, exDisplayBoolean, exDisplayImages, exDisplayMemo, exDisplayDateTime, exShowTextEllipsis, exShowTitleEllipsis, exFullSizeMemo, exAllowRowSizing, exCellHints, exMultiLineTitles, exUseRowColors, exPrintGrid, exPrintDataSet, exExportGrid, exSelectAll, exUnSelectAll, exCustomize, exSearchMode]
        Columns = <
          item
            Expanded = False
            FieldName = 'ID_BIBLIOTECA'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'ID_RED'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'NOMBRE_BIBLIOTECA'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'NOMBRE_DIRECTOR'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'EMAIL_DIRECTOR'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'DOMICILIO'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'CIUDAD'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'PROVINCIA'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'PAIS'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'TEMA'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'ARCHIVO_BANNER'
            Visible = True
          end
          item
            Expanded = False
            FieldName = 'IDIOMA'
            Visible = True
          end>
      end
    end
    object xpTabSheet2: TxpTabSheet
      Caption = 'Redes'
      Color = clWhite
      BGStyle = bgsNone
      GradientStartColor = clWhite
      GradientEndColor = clSilver
      GradientFillDir = fdTopToBottom
      object ExDBGrid2: TExDBGrid
        Left = 14
        Top = 12
        Width = 627
        Height = 269
        DataSource = DataSource2
        TabOrder = 0
        TitleFont.Charset = DEFAULT_CHARSET
        TitleFont.Color = clWindowText
        TitleFont.Height = -11
        TitleFont.Name = 'MS Sans Serif'
        TitleFont.Style = []
        ScrollBars = ssHorizontal
        EditColor = clWindow
        FixedRowHeight = 17
        DefaultRowHeight = 17
        RowColor1 = 12255087
        RowColor2 = clWindow
        HighlightColor = clNavy
        ImageHighlightColor = clWindow
        HighlightFontColor = clWhite
        HotTrackColor = clNavy
        LockedCols = 0
        LockedFont.Charset = DEFAULT_CHARSET
        LockedFont.Color = clWindowText
        LockedFont.Height = -11
        LockedFont.Name = 'MS Sans Serif'
        LockedFont.Style = []
        LockedColor = clGray
        ExMenuOptions = [exAutoSize, exAutoWidth, exDisplayBoolean, exDisplayImages, exDisplayMemo, exDisplayDateTime, exShowTextEllipsis, exShowTitleEllipsis, exFullSizeMemo, exAllowRowSizing, exCellHints, exMultiLineTitles, exUseRowColors, exPrintGrid, exPrintDataSet, exExportGrid, exSelectAll, exUnSelectAll, exCustomize, exSearchMode]
      end
    end
  end
  object DataSource1: TDataSource
    DataSet = ADOQuery1
    Left = 184
    Top = 8
  end
  object ADOQuery1: TADOQuery
    Connection = dmDatos.mDataBase1
    CursorType = ctStatic
    Parameters = <>
    SQL.Strings = (
      'SELECT * FROM cfgbiblioteca'
      'ORDER BY ID_BIBLIOTECA')
    Left = 144
    Top = 8
  end
  object ADOQuery2: TADOQuery
    Connection = dmDatos.mDataBase1
    CursorType = ctStatic
    Parameters = <>
    SQL.Strings = (
      'SELECT * FROM cfgredes'
      'ORDER BY ID_RED')
    Left = 256
    Top = 8
  end
  object DataSource2: TDataSource
    DataSet = ADOQuery2
    Left = 288
    Top = 8
  end
end
