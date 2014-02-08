(*******
  Historial de Cambios:

  FORMA DE CATALOGACION

   06-mar-2009: Se inicia.
   19-mar-2009: Se implementa la carga de la portada

 **)
unit Busquedas;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, ExtCtrls, ComCtrls, xpPages, cxLookAndFeelPainters, Buttons, cxButtons, cxLookAndFeels,
  StdCtrls, RoundButton, myform, Menus, Contnrs, Md5DeftEdit,
  dxsbar, xpGroupBox, cxControls, cxContainer, cxEdit,
  cxRadioGroup, cxMaskEdit, cxDropDownEdit, cxButtonEdit, DB,
  mQuery, cxTextEdit, ExDateTimePicker, cxStyles, cxCustomData, cxGraphics,
  cxFilter, cxData, cxDBData, cxGridCustomTableView, cxGridTableView,
  cxGridDBTableView, cxGridLevel, cxClasses, cxGridCustomView, cxGrid,
  ADODB, url_label, ALHintBalloon; {ALHintBalloon;}

type

  TfrmBusquedasTitulos = class(TFormSpecial)
    PopupMenu1: TPopupMenu;
    ALHintBalloonControl1: TALHintBalloonControl;
    Label1: TLabel;
    Label12: TLabel;
    cxRadXTitulo: TcxRadioButton;
    cxRadXAutor: TcxRadioButton;
    cxRadXEncabezamiento: TcxRadioButton;
    cxRadClasificacion: TcxRadioButton;
    cxRadISBNISSN: TcxRadioButton;
    btnBuscar: TEncarta;
    cxTextEdit1: TcxTextEdit;
    Label13: TLabel;
    winConsulta: TScrollBox;
    Label2: TLabel;
    cmbEstilo: TcxComboBox;

    procedure FormCreate(Sender: TObject);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);

    procedure btnBuscarClick(Sender: TObject);
    procedure FormDestroy(Sender: TObject);
    procedure OnImageClick(Sender: TObject);
    procedure FormResize(Sender: TObject);

  private
    { Private declarations }
    nTitulo: integer;

    aPanels: TObjectList;

    procedure LimpiarPanels;

    function AgregarPanel( nLine: integer; nLeft: integer; cCaption, cHint: string ): TPanel;

    function AgregarEtiqueta( thePanel: TPanel;
       nLine: integer; nLeft: integer; cCaption: string;
       fntStyle: TFontStyles; estiloLiga: boolean; cHint: string ): TCustomLabel;

  public

    { Public declarations }

  end;

  procedure ALHintBalloon_BIBLIOTEK(ALHintBalloonObject: TALHintBalloon);

implementation

uses Unit1, Datos, clases_app_biblio, jpeg;

var
   pCurImage: TImage;

{$R *.dfm}

procedure TfrmBusquedasTitulos.FormCreate(Sender: TObject);
begin

  Self.Top := 5;
  Self.Left := 5;

  Self.Width  := frmMenuPrincipal.Width - 50;
  Self.Height := frmMenuPrincipal.Height - 100;

  cmbEstilo.ItemIndex := 0;

  aPanels := TObjectList.Create;

end;

procedure TfrmBusquedasTitulos.FormClose(Sender: TObject;
  var Action: TCloseAction);
begin

  Action := caFree;

end;

procedure TfrmBusquedasTitulos.LimpiarPanels;
begin

   aPanels.Clear;

end;

function TfrmBusquedasTitulos.AgregarPanel( nLine: integer; nLeft: integer; cCaption, cHint: string ): TPanel;
var
   theLabel: TGESURL_Label;
begin

   Result := TPanel.Create( winConsulta );
   Result.Parent      := winConsulta;
   Result.Align       := alTop;
   Result.Height      := 50;

   // Agregar la primera etiqueta
   theLabel := TGESURL_Label.Create( Result );
   theLabel.Parent      := Result;

   theLabel.Caption     := cCaption;
   theLabel.Visible     := True;
   theLabel.Font.Style  := [fsBold];
   theLabel.Hint        := cHint; // objCampo.ObtenerDescripcion;
   theLabel.ShowHint    := true;
   theLabel.Top         := 5;
   theLabel.Left        := nLeft;
   theLabel.Transparent := true;
   //Result.OnClick     := GESURL_Label_Campo_Click;

   aPanels.Add( Result );

end;

function TfrmBusquedasTitulos.AgregarEtiqueta( thePanel: TPanel;
   nLine: integer; nLeft: integer; cCaption: string;
   fntStyle: TFontStyles; estiloLiga: boolean; cHint: string ): TCustomLabel;
begin

   // Agregar la primera etiqueta
   if estiloLiga then
      Result := TGESURL_Label.Create( thePanel )
   else
      Result := TLabel.Create( thePanel );

   Result.Parent      := thePanel;

   Result.Caption     := cCaption;
   Result.Visible     := True;
   TLabel(Result).Font.Style  := fntStyle;
   Result.Hint        := cHint;
   Result.ShowHint    := true;
   Result.Top         := nLine;
   Result.Left        := nLeft;
   TLabel(Result).Transparent := true;
   //Result.OnClick     := GESURL_Label_Campo_Click;

   if Result.Top + Result.Height + 10 > thePanel.Height then
      thePanel.Height := Result.Top + Result.Height + 15;

end;


procedure TfrmBusquedasTitulos.btnBuscarClick(Sender: TObject);
var
  newPanel: TPanel;
  objRegistroMARC21: TMARC21_Registro;

  apImage: TImage;
  oJpeg: TJpegImage;

  topLine: integer;

  cValor: string;

  AStrm: TMemoryStream;

  procedure AgregarEtiqueta_Campo( cIDCampo: string; cInitCaption: string = '' );
  var
     cVal: string;

  begin

     cVal := objRegistroMARC21.BuscarCampoMARC( cIDCampo ).ConcatenarValores;

     if cVal <> '' then
     begin
        cVal := cInitCaption + cVal;
        
        AgregarEtiqueta( newPanel, topLine, 60, cVal, [], false, 'Campo '+cIDCampo );
        topLine := topLine + 15;
     end;

  end;

begin

  // Buscar t�tulos
  //
  //
  dmDatos.qryAnySQL.SQL.Clear;
  dmDatos.qryAnySQL.SQL.Add( 'SELECT a.ID_TITULO, b.ID_TIPOMATERIAL, b.ID_SERIE, b.STATUS, b.FECHA_REGISTRO, b.PORTADA, b.CONTRAPORTADA ' );
  dmDatos.qryAnySQL.SQL.Add( 'FROM acervo_catalogacion a LEFT JOIN acervo_titulos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_TITULO=a.ID_TITULO) ' );

  if Self.cxRadXTitulo.Checked then
  begin
     // POR TITULO CAMPO 245, subcampo $a
     dmDatos.qryAnySQL.SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca)+' and ID_CAMPO="245" and CODIGO="$a" ' );
     dmDatos.qryAnySQL.SQL.Add( '  and VALOR LIKE "%'+cxTextEdit1.Text+'%" ' );
  end
  else if Self.cxRadXAutor.Checked then
  begin
     // POR AUTOR CAMPO 245, subcampo $c
     dmDatos.qryAnySQL.SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca)+' and ID_CAMPO="245" and CODIGO="$c" ' );
     dmDatos.qryAnySQL.SQL.Add( '  and VALOR LIKE "%'+cxTextEdit1.Text+'%" ' );
  end;

  dmDatos.qryAnySQL.SQL.Add( 'ORDER BY VALOR' );

  dmDatos.qryAnySQL.Open;

  nTitulo := 1;

  winConsulta.Visible := false;

  LimpiarPanels;

  while not dmDatos.qryAnySQL.Eof do
  begin

      // catalogacion
      objRegistroMARC21 := TMARC21_Registro.Create;
      objRegistroMARC21.InicializarRegistroMARC21_DesdeBD_Titulo( __IDBiblioteca,
                                                                  dmDatos.qryAnySQL.FieldByName('ID_TITULO').AsCurrency );

      // Concatenar valores
      cValor := objRegistroMARC21.BuscarCampoMARC('245').ConcatenarValores;
      //cValor2 := objRegistroMARC21.BuscarCampoMARC('260').ConcatenarValores;

      // Colocar como primera etiqueta el Campo 245
      newPanel := AgregarPanel( nTitulo, 10, cValor,
                                   dmDatos.qryAnySQL.FieldByName('ID_TITULO').AsString );

      with newPanel do
      begin

         topLine := 20;

         AStrm := TMemoryStream.Create ;

         TBlobField(dmDatos.qryAnySQL.FieldByName('PORTADA')).SaveToStream(AStrm);

         AStrm.Seek( 0, soFromBeginning );

         if AStrm.Size > 0 then
         begin
            oJpeg := TJpegImage.Create;
            oJpeg.LoadFromStream( AStrm );

            apImage := TImage.Create(newPanel);
            apImage.Parent := newPanel;
            apImage.Top    := topLine;
            apImage.Left   := 15;
            apImage.Height := 50;
            apImage.Width  := 35;
            apImage.Picture.Assign( oJpeg );
            apImage.Stretch := true;
            apImage.OnClick := OnImageClick;

            oJpeg.Free;
         end;
         
         AStrm.Free;


         AgregarEtiqueta_Campo( '260' ); // 260 - PUBLICACION, DISTRIB, ETC. (IMPRESI�N)

         AgregarEtiqueta_Campo( '300' );     // 300 - Extensi�n del ITEM

         // NOTAS
         AgregarEtiqueta_Campo( '500' ); // 500 - Nota GENERAL
         AgregarEtiqueta_Campo( '501' ); // 501 - Nota de CON
         AgregarEtiqueta_Campo( '502' ); // 502 - Nota de tesis
         AgregarEtiqueta_Campo( '504' ); // 504 - Nota de Bibliograf�a
         AgregarEtiqueta_Campo( '505' ); // 505 - Nota de contenido con formato

         AgregarEtiqueta_Campo( '020', 'ISBN ' ); // 020 - ISBN

      end;

      objRegistroMARC21.Free;

      dmDatos.qryAnySQL.Next;

      nTitulo := nTitulo + 1;

  end;

  dmDatos.qryAnySQL.Close;

  winConsulta.Visible := true;

  Application.ProcessMessages;
end;

procedure TfrmBusquedasTitulos.FormDestroy(Sender: TObject);
begin

  aPanels.Free;

end;

procedure ALHintBalloon_BIBLIOTEK(ALHintBalloonObject: TALHintBalloon);
var
//   LblText: TLabel;
   IcoImage: TImage;

begin

   {------------------------------------}
   ALHintBalloonObject.Color := $00E1FFFF;
   ALHintBalloonObject.Font.Name := 'Tahoma';
   ALHintBalloonObject.Font.Size := 8;
   ALHintBalloonObject.Font.Color := clBlack;

(*   LblText := TLabel.Create(ALHintBalloonObject.MainPanel);

   with LblText do
   begin
      Parent := ALHintBalloonObject.MainPanel;
      ParentColor := True;
      ParentFont := True;
      Caption := 'xx';
      Top     := 5;
   end;
  *)
   IcoImage := TImage.Create(ALHintBalloonObject.MainPanel);

   with IcoImage do
   begin
       Parent := ALHintBalloonObject.MainPanel;
       Transparent := True;
       Left := 5;
       Top := 5;
       Autosize := True;
       Picture.Assign( pCurImage.Picture );
       Invalidate;
       Onclick := ALHintBalloonObject.OnFormClick;
   end;

   IcoImage.Autosize := false;
   IcoImage.Stretch  := true;
   IcoImage.Height := IcoImage.Height div 2;
   IcoImage.Width  := IcoImage.Width div 2;

   IcoImage.Invalidate;

   ALHintBalloonObject.MainPanel.Height := IcoImage.Height + 10;
   ALHintBalloonObject.MainPanel.Width  := IcoImage.Width + 10;

end;

procedure TfrmBusquedasTitulos.OnImageClick(Sender: TObject);
begin

  pCurImage := TImage(Sender);

  ALHintBalloonControl1.ShowCustomHintBalloon(ALHintBalloon_BIBLIOTEK,
     '', 10, 10, TControl(Sender), bapTopLeft );

end;

procedure TfrmBusquedasTitulos.FormResize(Sender: TObject);
begin
   winConsulta.Width  := Self.Width - 40;
   winConsulta.Height := Self.Height - 140;
end;

end.
