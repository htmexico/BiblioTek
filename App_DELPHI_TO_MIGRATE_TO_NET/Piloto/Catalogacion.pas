(*******
  Historial de Cambios:

  FORMA DE CATALOGACION

   04-feb-2009: Se inicia.
   09-feb-2009: Trabajos para colocar los controles de edici�n auto-dimensionables.
   12-feb-2009: Se colocan los elementos del campo 008/Datos Generales
                Se inicia implementacion de clase TMarc21_Registro.
   19-feb-2008: Se logra escribir una catalogaci�n por 1era. vez.
                Se mejora la interface con los botones de la barra de herramientas.
   27-feb-2008: Se complementa y simplifica la interfaz de captura.
   06-mar-2009: Se implementa la posibilidad de agregar subcampos en run-time.
   18-mar-2009: Se implementa la digitalizacion de la portada y contraportada
   20-mar-2009: Se implementa el cruce/llenado de un subcampo desde un tesauro.
   25-mar-2009: Se agrega la caracter�stica para repetir ocurrencias
   25-mar-2009: Se agrega la caracter�stica para repetir campos

   26-mar-2009: Se inicia trabajo para manejar elementos por separado de la cabecera
                al momento de guardar un registr

   Pendiente:

   // Verificar agregar/quitar ocurencias

 **)
unit Catalogacion;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, ExtCtrls, ComCtrls, xpPages, cxLookAndFeelPainters, Buttons, cxButtons, cxLookAndFeels,
  StdCtrls, RoundButton, myform, Menus, Contnrs, Md5DeftEdit,
  dxsbar, xpGroupBox, clases_app_biblio, cxControls, cxContainer, cxEdit,
  cxRadioGroup, cxMaskEdit, cxDropDownEdit, cxButtonEdit, DB,
  mQuery, cxTextEdit, ExDateTimePicker, cxStyles, cxCustomData, cxGraphics,
  cxFilter, cxData, cxDBData, cxGridCustomTableView, cxGridTableView,
  cxGridDBTableView, cxGridLevel, cxClasses, cxGridCustomView, cxGrid,
  Grids, DBGrids, ExDBGrid, ADODB, url_label, ExtDlgs, ALHintBalloon;

const

  JPEG_Quality = 10;  // 10 alta,  5 = media,   2 = Baja

type

  TPosicionObj = class(TObject)
    private
      pControl: TDeftEdit;
      topOrigin: integer;
      nLineas: integer;

      aFriends: TObjectList;

    public
      objCampo: TMarc21_Campo;
      cIDSubCampo, cTag3: string;

      constructor Create( aControl: TDeftEdit; nTop: integer; nLines: integer; objCampo: TMarc21_Campo; c_SubCampo, c_Tag3 : string );
      destructor Destroy; override;

      procedure Agregar_Elemento_Relacionado( pControl: TControl );
      function Contar_Relacionados: integer;
      function Obtener_Relacionado( ndx: integer ): TObject;

      function BuscarBotonAux( objSender: TCXButton; nID: integer ): TCXButton;

      procedure ReUbicar( nIncremento: integer );
      procedure LiberarControl;

  end;

  TfrmCatalogacion = class(TFormSpecial)
    Panel1: TPanel;
    cxButton1: TcxButton;
    PopupMenu1: TPopupMenu;
    PopupTipoRegistro: TPopupMenu;
    PopupEstadoRegistro: TPopupMenu;
    PopupNivelBibliografico: TPopupMenu;
    PopupFormaCatalogacion: TPopupMenu;
    PopupNivelCodificacion: TPopupMenu;
    PopupTipoFechaEstPublicacion: TPopupMenu;
    PopupRegistroModificado: TPopupMenu;
    PopupFuenteCatalog: TPopupMenu;
    btnSave: TEncarta;
    btnCancel: TEncarta;
    btnAgregarOcurrencia: TcxButton;
    btnEliminarOcurrencia: TcxButton;
    btnAgregarCampo: TcxButton;
    btnEliminarCampo: TcxButton;
    popupInfoCampo: TPopupMenu;
    AgregarSubCampo1: TMenuItem;
    N1: TMenuItem;
    VerDocumentacin1: TMenuItem;
    ALHintBalloonControl1: TALHintBalloonControl;
    xpPageControl1: TxpPageControl;
    xpTabSheet1: TxpTabSheet;
    xpTabSheet2: TxpTabSheet;
    ScrollBox2: TScrollBox;
    Label1: TLabel;
    Label4: TLabel;
    Label11: TLabel;
    lblNoControl: TLabel;
    lblFechaCreacion: TLabel;
    Label14: TLabel;
    xpGroupBox1: TxpGroupBox;
    Label2: TLabel;
    Label3: TLabel;
    Label5: TLabel;
    Label6: TLabel;
    Label10: TLabel;
    cxTipoMaterialBtn: TcxButton;
    cxEstadoRegistroBtn: TcxButton;
    cxNivelBibliograficoBtn: TcxButton;
    cxFormaCatalogBtn: TcxButton;
    cxNivelCodificacionBtn: TcxButton;
    xpGroupBox2: TxpGroupBox;
    Label15: TLabel;
    Label16: TLabel;
    Label17: TLabel;
    Label18: TLabel;
    Label20: TLabel;
    Label19: TLabel;
    Label21: TLabel;
    cx008_TipoFechaEstPublicacion: TcxButton;
    cx008_Fecha1: TcxComboBox;
    cx008_Fecha2: TcxComboBox;
    cx008_RegistroModificado: TcxButton;
    cx008_FuenteCatalogacion: TcxButton;
    cx008_Idioma: TcxButtonEdit;
    cx008_LugarPublicacion: TcxButtonEdit;
    xpGroupBox3: TxpGroupBox;
    xpGroupBox4: TxpGroupBox;
    btnCopyPortada: TEncarta;
    btmPastePortada: TEncarta;
    btnCopyPortadaFromFile: TEncarta;
    btnCopyContra: TEncarta;
    btnPasteContra: TEncarta;
    btnCopyContraFromFile: TEncarta;
    Image1: TImage;
    Image2: TImage;
    OpenPictureDialog1: TOpenPictureDialog;
    Panel2: TPanel;
    StatusBar1: TStatusBar;
    ScrollBox1: TScrollBox;
    PanelIndicadoresEditor: TPanel;
    Panel4: TPanel;
    Label7: TLabel;
    lblCampo: TLabel;
    btnCloseIndicador: TcxButton;
    Panel5: TPanel;
    Label8: TLabel;
    cxRadioGroup1: TcxRadioGroup;
    cxComboBox1: TcxComboBox;
    Panel6: TPanel;
    Label9: TLabel;
    cxRadioGroup2: TcxRadioGroup;
    cxComboBox2: TcxComboBox;
    PanelPie: TPanel;
    btnSaveIDs: TcxButton;
    RepetirCampo1: TMenuItem;

    procedure FormCreate(Sender: TObject);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);
    procedure btnOpcionVariableClick(Sender: TObject);

    procedure ReUbicarControles( editBox: TDeftEdit; nLineasActuales: integer );

    procedure DeftEdit1Change(Sender: TObject);
    procedure DeftEdit1OnEnter(Sender: TObject);
    procedure PopupTipoRegistroPopup(Sender: TObject);
    procedure PopupEstadoRegistroPopup(Sender: TObject);
    procedure PopupNivelBibliograficoPopup(Sender: TObject);
    procedure PopupFormaCatalogacionPopup(Sender: TObject);
    procedure MenuItemTipoMaterial(Sender: TObject);

    procedure MenuItemEdoRegistroClick(Sender: TObject);
    procedure MenuItemNivelBibliogClick(Sender: TObject);
    procedure MenuItemNivelCodificacionClick(Sender: TObject);
    procedure MenuItemFormaCatalogClick(Sender: TObject);
    procedure MenuItemTipoFechaEstPublicacionClick(Sender: TObject);
    procedure MenuItemRegistroModificadoClick(Sender: TObject);
    procedure MenuItemFuenteCatalogacionClick(Sender: TObject);

    procedure btnCloseIndicadorClick(Sender: TObject);
    procedure PopupNivelCodificacionPopup(Sender: TObject);
    procedure PopupTipoFechaEstPublicacionPopup(Sender: TObject);
    procedure cx008_LugarPublicacionPropertiesButtonClick(Sender: TObject;
      AButtonIndex: Integer);
    procedure cx008_IdiomaPropertiesButtonClick(Sender: TObject;
      AButtonIndex: Integer);
    procedure PopupRegistroModificadoPopup(Sender: TObject);
    procedure PopupFuenteCatalogPopup(Sender: TObject);
    procedure btnSaveClick(Sender: TObject);
    procedure btnEliminarCampoClick(Sender: TObject);
    procedure btnSaveIDsClick(Sender: TObject);
    procedure btnCancelClick(Sender: TObject);
    procedure cxTextEdit1PropertiesValidate(Sender: TObject;
      var DisplayValue: Variant; var ErrorText: TCaption;
      var Error: Boolean);
    procedure btnAgregarCampoClick(Sender: TObject);

    procedure popupInfoCampoPopup(Sender: TObject);
    procedure GESURL_Label_Campo_Click(Sender: TObject);
    procedure GESURL_Label_SubCampo_Click(Sender: TObject);
    procedure AgregarSubCampo1Click(Sender: TObject);
    procedure btnCopyPortadaClick(Sender: TObject);
    procedure btmPastePortadaClick(Sender: TObject);
    procedure btnCopyPortadaFromFileClick(Sender: TObject);
    procedure btnCopyContraClick(Sender: TObject);
    procedure btnPasteContraClick(Sender: TObject);
    procedure RepetirCampo1Click(Sender: TObject);
    procedure btnAgregarOcurrenciaClick(Sender: TObject);
    procedure btnEliminarOcurrenciaClick(Sender: TObject);

  private
    { Private declarations }
    Editing: boolean;
    registroMarc21: TMARC21_Registro;

    aWinControls: TObjectList;
    ultimoEditActivado: TDeftEdit;

    editandoIndicadoresCampo: TMARC21_Campo;

    function Ubicar_Indice_Posicion( objPosicion: TPosicionObj ): integer;
    function Ubicar_EditBox_Posicion( editBox: TDeftEdit ): integer;
    function Ubicar_Campo_Posicion( cID_DEL_Campo: string ): integer;

    procedure Proc_Click_EN_IDENTIFICADOR(Sender: TObject);
    procedure Proc_Click_EN_AUXILIAR(Sender: TObject);

    function LocalizarBotones_DE_Indicadores( cID_DEL_Campo: string; nID: integer ): TCXButton;
    procedure ColocarValoresIndicadores( objCampo: TMARC21_Campo; nID: integer; cDescrip: string;
          objIndicadores: TMARC21_Identificador );

    function UbicarCampoYSubCampo( editBox: TDeftEdit;
            var objCampo: TMARC21_Campo; var objSubCampo: TMARC21_SubCampo;
            var objPosicion: TPosicionObj ) : boolean;

  public

    { Public declarations }
    procedure InitMenus( cCampo: string; cCodigo: string;
       popupMenu: TPopupMenu; cxButtonEdit: TCxButton; otroOrigen: string = '' );

    procedure InicializarMenuPlantillas;

    procedure LimpiarElementos( resetRegistroMARC: boolean );
    procedure CrearElementos( cIDPlantilla: string; resetDirectorioYCabecera: boolean );

    procedure HiliteItemSelected( cCodigo: string; popupMenu: TPopupMenu );

  end;

  procedure ConvertToJPEG( Image: TImage );


implementation

uses Unit1, Datos, DateUtils, SeleccionaValores, SeleccionaCampo,
  SeleccionaSubCampo, jpeg, clipbrd;

{$R *.dfm}

constructor TPosicionObj.Create( aControl: TDeftEdit; nTop, nLines : integer; objCampo: TMarc21_Campo; c_SubCampo, c_Tag3 : string );
begin

    pControl  := aControl;
    topOrigin := nTop;
    nLineas   := nLines;

    Self.objCampo := objCampo;

//    cIDCampo    := c_IDCampo;
    cIDSubCampo := c_SubCampo;
    cTag3       := c_Tag3;

    aFriends  := TObjectList.Create;
    aFriends.OwnsObjects := false;

end;

destructor TPosicionObj.Destroy;
var
    i: integer;
begin

    for i := 0 to aFriends.Count - 1 do
    begin

        if TLabel(aFriends.Items[i]) is TLabel then
           TLabel(aFriends.Items[i]).Free
        else if TGESURL_Label(aFriends.Items[i]) is TGESURL_Label then
           TGESURL_Label(aFriends.Items[i]).Free
        else if TControl(aFriends.Items[i]) is TShape then
           TShape(aFriends.Items[i]).Free
        else if TControl(aFriends.Items[i]) is TCXButton then
           TCXButton(aFriends.Items[i]).Free
        else
           ShowMessage( 'Error en componente' );

    end;

    aFriends.Free;  // aqu� se destruyen todos los objetos

end;

procedure TPosicionObj.ReUbicar( nIncremento: integer );
var
    i: integer;

begin

    pControl.Top := pControl.Top + nIncremento;

    for i := 0 to aFriends.Count - 1 do
    begin

        if TControl(aFriends.Items[i]) is TLabel then
           TLabel(aFriends.Items[i]).Top := TLabel(aFriends.Items[i]).Top + nIncremento
        else if TControl(aFriends.Items[i]) is TGESURL_Label then
           TGESURL_Label(aFriends.Items[i]).Top := TGESURL_Label(aFriends.Items[i]).Top + nIncremento
        else if TControl(aFriends.Items[i]) is TShape then
           TShape(aFriends.Items[i]).Top := TShape(aFriends.Items[i]).Top + nIncremento
        else if TControl(aFriends.Items[i]) is TCXButton then
           TCXButton(aFriends.Items[i]).Top := TCXButton(aFriends.Items[i]).Top + nIncremento

    end;

end;

procedure TPosicionObj.Agregar_Elemento_Relacionado( pControl: TControl );
begin

    aFriends.Add( pControl );

end;

function TPosicionObj.Contar_Relacionados: integer;
begin

    Result := aFriends.Count;

end;

function TPosicionObj.Obtener_Relacionado( ndx: integer ): TObject;
begin

    Result := nil;

    if ndx > aFriends.Count -1 then
       Result := aFriends[ndx];

end;

//
// Busca un boton relacionado dentro del objeto TPosicionObj
//
function TPosicionObj.BuscarBotonAux( objSender: TCXButton; nID: integer ): TCXButton;
var
    i: integer;
begin

    Result := nil;

    if objSender <> nil then
    begin

       for i := 0 to aFriends.Count - 1 do
       begin

            if aFriends.Items[i] is TCXButton then
               if aFriends.Items[i] = objSender then
               begin
                  Result := TCXButton(aFriends.Items[i]);
                  break;
               end;

       end;

    end
    else if nID <> 0 then
    begin

       for i := 0 to aFriends.Count - 1 do
           if aFriends.Items[i] is TCXButton then
           begin

             if (TCXButton(aFriends.Items[i]).Layout = blGlyphLeft) and (nID = 1) then
                Result := TCXButton(aFriends.Items[i])
             else if (TCXButton(aFriends.Items[i]).Layout = blGlyphRight) and (nID = 2) then
                Result := TCXButton(aFriends.Items[i]);

           end;

    end;

end;

procedure TPosicionObj.LiberarControl;
begin

    pControl.Free;

(*    if pControl is TLabel then
       TLabel(pControl).Free
    else if pControl is TShape then
       TShape(pControl).Free
    else if pControl is TDeftEdit then
       TDeftEdit(pControl).Free
    else if pControl is TCXButton then
       TCXButton(pControl).Free;
//    else
//    begin
//       ShowMessage( ' Control desconocido ' );
//    end;
 *)
 
end;

procedure TfrmCatalogacion.FormCreate(Sender: TObject);
begin

  Self.Top := 5;
  Self.Left := 5;

  Self.Width  := frmMenuPrincipal.Width - 50;
  Self.Height := frmMenuPrincipal.Height - 100;

  InicializarMenuPlantillas;

  aWinControls := TObjectList.Create;
  aWinControls.OwnsObjects := false;

  registroMarc21 := TMARC21_Registro.Create;  // Crear registro MARC21

  PopupTipoRegistro.Items.Clear;

  Editing := false;

  ultimoEditActivado := nil;
  editandoIndicadoresCampo := nil;

end;

procedure TfrmCatalogacion.FormClose(Sender: TObject;
  var Action: TCloseAction);
begin

  LimpiarElementos(true);

  if aWinControls <> nil then
  begin
     aWinControls.Free;
     aWinControls := nil;
  end;

  if registroMarc21 <> nil then
  begin
     registroMarc21.Free;  // Destruir registro MARC21
     registroMarc21 := nil;
  end;

  Action := caFree;

end;

// 04feb2009
// Crea los campos en tiempo de ejecuci�n seg�n la selecci�n del usuario
// desde PLANTILLAS
//
// en Caption viene el nombre de la plantilla
// en Hint viene el n�m o ID de la plantilla
//
procedure TfrmCatalogacion.btnOpcionVariableClick( Sender: TObject );
begin

    // inicializar campos directorio / encabezado / generales
    cxTipoMaterialBtn.Caption       := '';
    cxEstadoRegistroBtn.Caption     := '';
    cxNivelBibliograficoBtn.Caption := '';
    cxNivelCodificacionBtn.Caption  := '';
    cxFormaCatalogBtn.Caption       := '';

    cx008_TipoFechaEstPublicacion.Caption  := '';

    cx008_Fecha1.Text := '';
    cx008_Fecha2.Text := '';

    cx008_LugarPublicacion.Text := '';
    cx008_Idioma.Text           := '';

    cx008_RegistroModificado.Caption       := '';
    cx008_FuenteCatalogacion.Caption       := '';

    LimpiarElementos( true );
    CrearElementos( TMenuItem(Sender).Hint, true );

    Editing := false;

    btnSave.Visible   := true;
    btnCancel.Visible := true;

    btnAgregarOcurrencia.Visible  := true;
    btnEliminarOcurrencia.Visible := true;

    btnAgregarCampo.Visible := true;
    btnEliminarCampo.Visible := true;

    Caption := 'Catalogaci�n -- ' + TMenuItem(Sender).Caption;

    Image1.Picture := nil;
    Image2.Picture := nil;

end;


// Inicializa los elementos del men�
// 04feb2009
procedure TfrmCatalogacion.InicializarMenuPlantillas;
var
   menuItem: TMenuItem;

begin

   with dmDatos.qryAnySQL do
   begin

       SQL.Clear;
       SQL.Add( 'SELECT ID_PLANTILLA, NOMBRE_PLANTILLA ' );
       SQL.Add( 'FROM cfgplantillas_nombres' );
       SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca)+' and ID_TIPO="CAT" ' );
       SQL.Add( 'ORDER BY ID_PLANTILLA' );
       Open;

       while not Eof do
       begin

          menuItem := TMenuItem.Create(nil);
          menuItem.Caption := FieldByName('NOMBRE_PLANTILLA').AsString;
          menuItem.Hint    := FieldByName('ID_PLANTILLA').AsString;

          menuItem.OnClick := btnOpcionVariableClick;

          PopupMenu1.Items.Add( menuItem );

          Next;

       end;

       Close;

   end;

end;

// 10feb2009
// Crea Popup's de CABECERA
procedure TfrmCatalogacion.InitMenus( cCampo: string; cCodigo: string;
    popupMenu: TPopupMenu; cxButtonEdit: TCxButton; otroOrigen: string = '' );
var
   menuItem: TMenuItem;
begin

   If popupMenu.Items.Count > 0 then exit;

   with dmDatos.qryAnySQL do
   begin

       SQL.Clear;

       if otroOrigen = '' then
       begin
           SQL.Add( 'SELECT SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, OBSOLETO ' );
           SQL.Add( 'FROM marc_codigo21' );
           SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and CODIGO="'+cCodigo+'" and SUBCODIGO<>"" and NIVEL_MARC=3 ' );
           SQL.Add( 'ORDER BY SUBCODIGO' );
       end
       else if otroOrigen = 'MARC_MATERIAL' then
       begin
           SQL.Add( 'SELECT ID_TIPOMATERIAL AS SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, OBSOLETO, CODIGO_MARC ' );
           SQL.Add( 'FROM marc_material' );
           SQL.Add( 'ORDER BY ORDENARPOR' );
       end;

       Open;

       while not Eof do
       begin

          menuItem := TMenuItem.Create(nil);
          menuItem.Caption := FieldByName('SUBCODIGO').AsString+'- '+FieldByName('DESCRIPCION').AsString;
          menuItem.Hint    := FieldByName('SUBCODIGO').AsString;

          if FieldByName('OBSOLETO').AsString = 'S' then
             menuItem.Enabled := false;

          if cxButtonEdit.Caption = FieldByName('SUBCODIGO').AsString then
             menuItem.Checked := true;

          if cCampo = '$$$' then
          begin
             if cCodigo = '06' then
                menuItem.OnClick := MenuItemTipoMaterial;
             if cCodigo = '05' then
                menuItem.OnClick := MenuItemEdoRegistroClick;
             if cCodigo = '07' then
                menuItem.OnClick := MenuItemNivelBibliogClick;
             if cCodigo = '17' then
                menuItem.OnClick := MenuItemNivelCodificacionClick; // xxx
             if cCodigo = '18' then
                menuItem.OnClick := MenuItemFormaCatalogClick;
          end;

          if cCampo = '008' then
          begin
             if cCodigo = '06' then
                menuItem.OnClick := MenuItemTipoFechaEstPublicacionClick
             else if cCodigo = '38' then
                menuItem.OnClick := MenuItemRegistroModificadoClick
             else if cCodigo = '39' then
                menuItem.OnClick := MenuItemFuenteCatalogacionClick
          end;

          popupMenu.Items.Add( menuItem );

          Next;

       end;

       Close;

   end;

end;


// Elimina los elementos que pueden existir en pantalla
// antes de crear nuevos elementos de entrada
// 04feb2009
procedure TfrmCatalogacion.LimpiarElementos( resetRegistroMARC: boolean );
var
  i: integer;

begin

  if aWinControls = nil then exit;

    // borrar controles en pantalla
  for i := 0 to aWinControls.Count - 1 do
  begin
      TPosicionObj(aWinControls.Items[i]).LiberarControl;
      TPosicionObj(aWinControls.Items[i]).Free;  // destruir aFriends
  end;

  aWinControls.Clear;
  Application.ProcessMessages;

  if resetRegistroMARC then
  begin
      // borrar campos MARC abiertos
      registroMarc21.Clear;
  end;

end;

// Crear los elementos Edit para hacer catalogaci�n
// 04feb2009
procedure TfrmCatalogacion.CrearElementos( cIDPlantilla: string; resetDirectorioYCabecera: boolean );
var
   xLabelIDCampo: TGESURL_Label;

   xLabelSubCampo: TGESURL_Label;
   xLabelDescripSubCampo: TLabel;

   xEditSpecial: TDeftEdit;

   i, j: integer;

   topLine: integer;
   maxLeft: integer;

   nLeft: integer;

   objCampo: TMARC21_Campo;

   objSubCampo: TMARC21_SubCampo;

   myLineSep, myLineSepIDs: TShape;
   myShapeIDCampo: TShape;

   myButtonsShape: TShape;
   myButtonID1, myButtonID2, auxButton: TCXButton;

   objPosicionCtrl: TPosicionObj;

   // inicializa elementos de captura TCXButton, con los valores
   // default y el Hint con la descripci�n
   procedure InicializarElementos_Cabecera;
   begin

     cxTipoMaterialBtn.Caption := registroMARC21.Encabezado_TipoRegistro;
     cxTipoMaterialBtn.Hint    := registroMARC21.ObtenerDescripcionSubcodigo( '$$$', '06', cxTipoMaterialBtn.Caption );

     cxEstadoRegistroBtn.Caption  := registroMARC21.Encabezado_EstadoRegistro;
     cxEstadoRegistroBtn.Hint     := registroMARC21.ObtenerDescripcionSubcodigo( '$$$', '05', cxEstadoRegistroBtn.Caption );

     cxNivelBibliograficoBtn.Caption := registroMARC21.Encabezado_NivelBibliografico;
     cxNivelBibliograficoBtn.Hint    := registroMARC21.ObtenerDescripcionSubcodigo( '$$$', '07', cxNivelBibliograficoBtn.Caption );

     cxNivelCodificacionBtn.Caption := registroMARC21.Encabezado_NivelCodificacion;
     cxNivelCodificacionBtn.Hint    := registroMARC21.ObtenerDescripcionSubcodigo( '$$$', '17', cxNivelCodificacionBtn.Caption );

     cxFormaCatalogBtn.Caption    := registroMARC21.Encabezado_FormaCatalogacion;
     cxFormaCatalogBtn.Hint       := cxFormaCatalogBtn.Caption + ' ' +registroMARC21.ObtenerDescripcionSubcodigo( '$$$', '18', cxFormaCatalogBtn.Caption );

   end;

   procedure InicializarElementos_Campo008( obj_Campo: TMARC21_Campo );
   begin

        cx008_TipoFechaEstPublicacion.Caption := registroMARC21.F008_TipoFechaEstadoPub;
        cx008_TipoFechaEstPublicacion.Hint    := registroMARC21.ObtenerDescripcionSubcodigo( '008', '06', cx008_TipoFechaEstPublicacion.Caption );

        // proviene de TESAURO Categoria 64
        cx008_LugarPublicacion.Text := registroMARC21.F008_LugarPublicacion;
        cx008_LugarPublicacion.Hint := registroMarc21.ObtenerDescripcionElementoTesauro( 'T64', cx008_LugarPublicacion.Text );

        // proviene de TESAURO Categoria 62
        cx008_Idioma.Text := registroMARC21.F008_Idioma;
        cx008_Idioma.Hint := registroMarc21.ObtenerDescripcionElementoTesauro( 'T62', cx008_Idioma.Text );

        cx008_RegistroModificado.Caption := registroMARC21.F008_RegistroModificado;
        cx008_RegistroModificado.Hint    := cx008_RegistroModificado.Caption+' '+registroMARC21.ObtenerDescripcionSubcodigo( '008', '38', cx008_RegistroModificado.Caption );

        cx008_FuenteCatalogacion.Caption := registroMARC21.F008_FuenteCatalogacion;
        cx008_FuenteCatalogacion.Hint    := cx008_FuenteCatalogacion.Caption+' '+registroMARC21.ObtenerDescripcionSubcodigo( '008', '39', cx008_FuenteCatalogacion.Caption );

   end;

begin

   if cIDPlantilla <> '' then
      registroMARC21.InicializarRegistroMARC21_DesdePlantilla( __IDBiblioteca, StrToInt(cIDPlantilla) );

   topLine := 10;
   maxLeft := 0;

   ScrollBox1.Visible := false;

   for i := 0 to registroMARC21.ContarCampos - 1 do
   begin

      objCampo := registroMARC21.ObtenerCampoMARC(i);

      if objCampo.bEncabezado then
      begin
          if resetDirectorioYCabecera then
             InicializarElementos_Cabecera; // inicia elementos de captura en pantalla
          continue;
      end;

      if objCampo.bDirectorio then
         continue;

      if objCampo.cIDCampo = '008' then
      begin
         if resetDirectorioYCabecera then
            InicializarElementos_Campo008( objCampo );
         continue;
      end;

      myLineSep := nil;

      myButtonsShape := nil;
      myLineSepIDs := nil;

      if( aWinControls.Count > 0 ) then
      begin
          //
          // LINEA de separaci�n entre campos
          //
          myLineSep := TShape.Create( ScrollBox1 );
          myLineSep.Parent    := ScrollBox1;
          myLineSep.Top       := topLine-3;
          myLineSep.Left      := 5;
          myLineSep.Height    := 1;
          myLineSep.Width     := ScrollBox1.Width - 20;
          myLineSep.Pen.Color := clSilver;

          topLine := topLine + 5;
      end;

      // Recuadro del ID del Campo
      myShapeIDCampo := TShape.Create( ScrollBox1 );
      myShapeIDCampo.Parent    := ScrollBox1;
      myShapeIDCampo.Top       := topLine - 3;
      myShapeIDCampo.Left      := 10;
      myShapeIDCampo.Height    := 20;
      myShapeIDCampo.Width     := 33;
      myShapeIDCampo.Pen.Color := clGray;

      // ID del Campo
      xLabelIDCampo := TGESURL_Label.Create( ScrollBox1 );
      xLabelIDCampo.Caption     := objCampo.cIDCampo;
      xLabelIDCampo.Parent      := ScrollBox1;
      xLabelIDCampo.Visible     := True;
      xLabelIDCampo.Font.Style  := [fsBold];
      xLabelIDCampo.Hint        := objCampo.ID;
      xLabelIDCampo.ShowHint    := false;
      xLabelIDCampo.Top         := topLine;
      xLabelIDCampo.Left        := 14;
      xLabelIDCampo.Transparent := true;
      xLabelIDCampo.PopupMenu   := popupInfoCampo;
      xLabelIDCampo.OnClick     := GESURL_Label_Campo_Click;

      // Colocar identificadores
      nLeft := 50;

      if (objCampo.objID1 <> nil) or (objCampo.objID2 <> nil) then
      begin
          myButtonsShape := TShape.Create( ScrollBox1 );
          myButtonsShape.Parent    := ScrollBox1;
          myButtonsShape.Top       := topLine - 3;
          myButtonsShape.Left      := nLeft-2;
          myButtonsShape.Height    := 20;
          myButtonsShape.Width     := 37;
          myButtonsShape.Pen.Color := clGray;

          if (objCampo.objID1 <> nil) and (objCampo.objID2 <> nil) then
          begin
              // separador entre botones
              myLineSepIDs := TShape.Create( ScrollBox1 );
              myLineSepIDs.Parent    := ScrollBox1;
              myLineSepIDs.Top       := topLine + 1;
              myLineSepIDs.Left      := nLeft+16;
              myLineSepIDs.Height    := 13;
              myLineSepIDs.Width     := 1;
              myLineSepIDs.Pen.Color := clSilver;
          end;
      end;

      if objCampo.objID1 <> nil then
      begin
          myButtonID1 := TCXButton.Create( ScrollBox1 );
          myButtonID1.Parent  := ScrollBox1;
          myButtonID1.Caption   := objCampo.objID1.Valor;
          myButtonID1.Top       := topLine-2;
          myButtonID1.Hint      := objCampo.objID1.ObtenerDescripcion;
          myButtonID1.ShowHint  := true;
          myButtonID1.Layout    := blGlyphLeft;
          myButtonID1.Left      := nLeft;
          myButtonID1.Height    := 18;
          myButtonID1.Width     := 15;
          myButtonID1.LookAndFeel.Kind := lfStandard;
          myButtonID1.OnClick   := Proc_Click_EN_IDENTIFICADOR;
          myButtonID1.Name      := 'AUXID1_'+objCampo.ID;
      end;

      nLeft := 68;

      if objCampo.objID2 <> nil then
      begin
          myButtonID2 := TCXButton.Create( ScrollBox1 );
          myButtonID2.Parent  := ScrollBox1;
          myButtonID2.Caption   := objCampo.objID2.Valor;
          myButtonID2.Top       := topLine-2;
          myButtonID2.Layout    := blGlyphRight;
          myButtonID2.Hint      := objCampo.objID2.ObtenerDescripcion;
          myButtonID2.ShowHint  := true;
          myButtonID2.Left      := nLeft;
          myButtonID2.Height    := 18;
          myButtonID2.Width     := 15;
          myButtonID2.LookAndFeel.Kind := lfStandard;
          myButtonID2.OnClick   := Proc_Click_EN_IDENTIFICADOR;
          myButtonID2.Name      := 'AUXID2_'+objCampo.ID;
      end;

      nLeft := 93;

      // colocar subcampos
      for j := 0 to registroMARC21.ContarCampos do
      begin
          objSubCampo := objCampo.SubCampo(j);

          if objSubCampo <> nil then
          begin

             // Etiqueta del subcampo
             xLabelSubCampo := TGESURL_Label.Create( ScrollBox1 );
             xLabelSubCampo.Caption     := objSubCampo.cIDSubCampo;
             xLabelSubCampo.Parent      := ScrollBox1;
             xLabelSubCampo.Visible     := True;
             xLabelSubCampo.Transparent := True;
             xLabelSubCampo.Hint        := objCampo.ID;

             xLabelSubCampo.Top     := topLine;
             xLabelSubCampo.Left    := nLeft;
             xLabelSubCampo.Font.Style := [fsBold];
             xLabelSubCampo.OnClick    := GESURL_Label_SubCampo_Click;

             // Descripci�n del Sub-Campo
             xLabelDescripSubCampo := TLabel.Create( ScrollBox1 );
             xLabelDescripSubCampo.Caption := objSubCampo.ObtenerDescripcion;
             xLabelDescripSubCampo.Parent  := ScrollBox1;
             xLabelDescripSubCampo.Visible := True;

             xLabelDescripSubCampo.Top     := topLine;
             xLabelDescripSubCampo.Left    := nLeft + 21;

             if objCampo.bObsoleto then
             begin
                xLabelSubCampo.Font.Style := xLabelSubCampo.Font.Style + [fsItalic];
                xLabelDescripSubCampo.Font.Style := [fsItalic];
             end;

             // Crear EditSpecial
             xEditSpecial := TDeftEdit.Create( ScrollBox1 );
             xEditSpecial.Parent := ScrollBox1;

             xEditSpecial.Top     := topLine - 3;
             xEditSpecial.Left    := nLeft + 250;
             xEditSpecial.Height  := 20;
             xEditSpecial.Width   := 400;
             xEditSpecial.Hint    := ''; //IntToStr(xEditSpecial.Width);
             xEditSpecial.ParentFont := true;
             xEditSpecial.OnChange   := DeftEdit1Change;
             xEditSpecial.OnEnter    := DeftEdit1OnEnter;
             xEditSpecial.MultiLine  := TRUE;
             xEditSpecial.WordWrap   := TRUE;
             xEditSpecial.Hint       := objSubCampo.ID;

             xEditSpecial.Text := objSubCampo.Valor;

             // xEditSpecial.Text       := objCampo.ID;   // DEBUGING

             if objSubCampo.cRepetible_SN = 'S' then
             begin
                //

             end;

             objPosicionCtrl := TPosicionObj.Create( xEditSpecial, xEditSpecial.Top, 1,
                                   objCampo, objSubCampo.cIDSubCampo, '' );

             if( j = 0 ) then
             begin
                 // los botones de indicador solo van
                 // cuando es el primer subcampo que se incorpora

                 if myLineSep <> nil then
                    objPosicionCtrl.Agregar_Elemento_Relacionado( myLineSep );

                 if myButtonsShape <> nil then
                    objPosicionCtrl.Agregar_Elemento_Relacionado( myButtonsShape );

                 if myLineSepIds <> nil then
                    objPosicionCtrl.Agregar_Elemento_Relacionado( myLineSepIds );

                 objPosicionCtrl.Agregar_Elemento_Relacionado( myShapeIDCampo );
                 objPosicionCtrl.Agregar_Elemento_Relacionado( xLabelIDCampo );

                 if objCampo.objID1 <> nil then
                    objPosicionCtrl.Agregar_Elemento_Relacionado( myButtonID1 );

                 if objCampo.objID2 <> nil then
                    objPosicionCtrl.Agregar_Elemento_Relacionado( myButtonID2 );
             end;

             // todos los Edits tienen un label asociado
             objPosicionCtrl.Agregar_Elemento_Relacionado( xLabelSubCampo );
             objPosicionCtrl.Agregar_Elemento_Relacionado( xLabelDescripSubCampo );

             if objSubCampo.cTesauro <> '' then
             begin
                xEditSpecial.Width   := 300;

                auxButton := TCXButton.Create( ScrollBox1 );
                auxButton.Parent  := ScrollBox1;
                auxButton.Caption   := '?';
                auxButton.Top       := topLine-2;
                auxButton.Layout    := blGlyphRight;
                auxButton.Hint      := objCampo.ID+':'+objSubCampo.ID;
                auxButton.ShowHint  := false;
                auxButton.Left      := xEditSpecial.Left + 3 + xEditSpecial.Width;
                auxButton.Height    := 18;
                auxButton.Width     := 15;
                auxButton.LookAndFeel.Kind := lfStandard;
                auxButton.OnClick   := Proc_Click_EN_AUXILIAR;

                objPosicionCtrl.Agregar_Elemento_Relacionado( auxButton );
             end;

             aWinControls.Add( objPosicionCtrl );

             // avanzar linea
             topLine := topLine + 25;
          end;

      end;

      if objCampo.ContarSubCampos = 0 then
      begin
         topLine := topLine + 25;
      end;

   end;

   ScrollBox1.Visible := true;

end;

// Permite reubicar los controles que est�n por debajo de un
// EDIT.
// Sirve para redimensionar un objeto TEdit
// o para cuando se quita un subcampo
procedure TfrmCatalogacion.ReUbicarControles( editBox: TDeftEdit; nLineasActuales: integer );
var
  i, j: integer;
  nIncremento: integer;

begin

//  ScrollBox1.Visible := false;

  for i := 0 to aWinControls.Count - 1 do
  begin

      if TPosicionObj( aWinControls.Items[i] ).pControl = editBox then
      begin

         nIncremento := 0;

         if nLineasActuales > TPosicionObj( aWinControls.Items[i] ).nLineas then
            nIncremento := (nLineasActuales - TPosicionObj( aWinControls.Items[i] ).nLineas) * 20
         else if TPosicionObj( aWinControls.Items[i] ).nLineas > nLineasActuales then
            nIncremento := ((TPosicionObj( aWinControls.Items[i] ).nLineas-nLineasActuales) * -20);

         TPosicionObj( aWinControls.Items[i] ).nLineas := nLineasActuales;

         if nLineasActuales = 0 then
         begin
            // quiere decir que el control est� siendo
            // eliminado por el usuario
            nIncremento := nIncremento - 6;
         end;

         // Redimensionar hacia abajo
         for j := i+1 to aWinControls.Count - 1 do
             TPosicionObj( aWinControls.Items[j] ).Reubicar( nIncremento );

         break;
      end;

  end;

//  ScrollBox1.Visible := true;

end;


procedure TfrmCatalogacion.DeftEdit1Change(Sender: TObject);
var
  nLinesInText: integer;
  nLinesInControl: integer;

  objCampo : TMARC21_Campo;
  objSubCampo: TMARC21_SubCampo;
  objPosicion: TPosicionObj;

begin

   If TDeftEdit(Sender).Modified then
   begin
       if UbicarCampoYSubCampo( TDeftEdit(Sender), objCampo, objSubCampo, objPosicion ) then
       begin

          objSubCampo.Valor := TDeftEdit(Sender).Text;

          nLinesInText := SendMessage( TDeftEdit(Sender).Handle, EM_GETLINECOUNT, 0, 0 );

          nLinesInControl := TDeftEdit(Sender).Height div 20;

          if nLinesInText <> nLinesInControl then
          begin
             TDeftEdit(Sender).Height := 20 * nLinesInText;

             ReUbicarControles( TDeftEdit(Sender), nLinesInText );

             TDeftEdit(Sender).SetFocus;
          end;

       end;
   end;

end;

function TfrmCatalogacion.UbicarCampoYSubCampo( editBox: TDeftEdit;
     var objCampo: TMARC21_Campo; var objSubCampo: TMARC21_SubCampo;
     var objPosicion: TPosicionObj ): boolean;
var
  ndx: integer;

begin

  Result := false;

  objCampo    := nil;
  objSubCampo := nil;
  objPosicion := nil;

  ndx := Ubicar_EditBox_Posicion( editBox );

  if ndx <> -1 then
  begin

     objCampo := registroMARC21.BuscarCampoMARC_X_ID( TPosicionObj( aWinControls.Items[ndx] ).objCampo.ID );

     if objCampo <> nil then
     begin
        //objSubCampo := objCampo.BuscarSubCampo_X_IDs( TPosicionObj( aWinControls.Items[ndx] ).cIDSubCampo );
        objSubCampo := objCampo.BuscarSubCampo_X_ID( editBox.Hint );

        Result := objSubCampo <> nil;

        if Result then
           objPosicion := TPosicionObj( aWinControls.Items[ndx] );

     end; { if }

  end; { if }


end;


procedure TfrmCatalogacion.DeftEdit1OnEnter(Sender: TObject);
var
   objCampo : TMARC21_Campo;
   objSubCampo: TMARC21_SubCampo;

   objPosicion: TPosicionObj;

begin

   if UbicarCampoYSubCampo( TDeftEdit(Sender), objCampo, objSubCampo, objPosicion ) then
   begin
      StatusBar1.Panels[0].Text := objCampo.cIDCampo + ' ' + objSubCampo.cIDSubCampo + ' - ' + objSubCampo.ObtenerDescripcion;

      btnAgregarOcurrencia.Enabled  := objSubCampo.cRepetible_SN = 'S';

      btnEliminarOcurrencia.Enabled := false;

      if objCampo.ContarOcurrencias( objSubCampo.cIDSubCampo ) > 1 then
         btnEliminarOcurrencia.Enabled := objSubCampo.cRepetible_SN = 'S';

      if objSubCampo.cRepetible_SN = 'S' then
         StatusBar1.Panels[1].Text := 'Repetible'
      else
         StatusBar1.Panels[1].Text := '';

      if objCampo.cRepetible_SN = 'S' then
         StatusBar1.Panels[2].Text := 'Campo Repetible'
      else
         StatusBar1.Panels[2].Text := '';

      ultimoEditActivado := TDeftEdit(Sender);

   end; { if }

end;

//
// Devuelve el indice dentro de aControls
// del campo editBox que se est� editando
function TfrmCatalogacion.Ubicar_EditBox_Posicion( editBox: TDeftEdit ): integer;
var
    i: integer;
begin

    Result := -1;

    for i := 0 to aWinControls.Count - 1 do
    begin

        if TPosicionObj( aWinControls.Items[i] ).pControl = editBox then
        begin
           Result := i;
           break;
        end;

    end;

end;

function TfrmCatalogacion.Ubicar_Campo_Posicion( cID_DEL_Campo: string ): integer;
var
    i: integer;
begin

    Result := -1;

    for i := 0 to aWinControls.Count - 1 do
    begin

        if TPosicionObj( aWinControls.Items[i] ).objCampo.ID = cID_DEL_Campo then
        begin
           Result := i;
           break;
        end;

    end;

end;

//
// Obtener la posici�n de un objeto TPosicionObj
// dentro del Array aWinControls
//

function TfrmCatalogacion.Ubicar_Indice_Posicion( objPosicion: TPosicionObj ): integer;
var
    i: integer;
begin

    Result := -1;

    for i := 0 to aWinControls.Count - 1 do
    begin

        if TPosicionObj( aWinControls.Items[i] ) = objPosicion then
        begin
           Result := i;
           break;
        end;

    end;

end;

// Eliminar un SubCampo/Campo
procedure TfrmCatalogacion.btnEliminarCampoClick(Sender: TObject);
var
   flag: boolean;

   objCampo : TMARC21_Campo;
   objSubCampo: TMARC21_SubCampo;

   objPosicion: TPosicionObj;

   pos: integer;

begin

  if ultimoEditActivado <> nil then
      if UbicarCampoYSubCampo( ultimoEditActivado, objCampo, objSubCampo, objPosicion ) then
      begin

         flag := objSubCampo.Valor = '';

         if not flag then
         begin
            flag := Self.DisplayQuestionMessage( '� Desea eliminar este subcampo que tiene contenido ?', 'Importante' );

         end;

         if flag then
         begin

            pos := Ubicar_Indice_Posicion( objPosicion );

            // REUBICAR CONTROLES POR DEBAJO
            if objCampo.SubCampo(0) = objSubCampo then
            begin
               registroMarc21.EliminarSubCampo( objCampo, objSubCampo );

               LimpiarElementos( false );
               CrearElementos( '', false );

               if pos-1 >= 0 then
                  TWinControl(TPosicionObj( aWinControls.Items[pos-1] ).pControl).SetFocus
               else if pos <= aWinControls.Count then
                  TWinControl(TPosicionObj( aWinControls.Items[pos] ).pControl).SetFocus

            end
            else
            begin
                ReUbicarControles( ultimoEditActivado, 0 );

                registroMarc21.EliminarSubCampo( objCampo, objSubCampo );

                objPosicion.LiberarControl;  // destruir control relacionado
                objPosicion.Free;

                if pos <> -1 then
                   aWinControls.Delete( pos );

                if pos-1 > 0 then
                   TWinControl(TPosicionObj( aWinControls.Items[pos-1] ).pControl).SetFocus;

            end;

         end;

      end;

end;


procedure TfrmCatalogacion.PopupTipoRegistroPopup(Sender: TObject);
begin

   // Abrir tipos de Registro
   // el contenido se llenar� de la tabla 'MARC_MATERIAL'
   InitMenus( '$$$', '06', PopupTipoRegistro, cxTipoMaterialBtn, 'MARC_MATERIAL' );

end;

procedure TfrmCatalogacion.PopupEstadoRegistroPopup(Sender: TObject);
begin

   // Abrir Estado de Registro
   InitMenus( '$$$', '05', PopupEstadoRegistro, cxEstadoRegistroBtn );

end;

procedure TfrmCatalogacion.PopupNivelBibliograficoPopup(Sender: TObject);
begin

   // Abrir Nivel Bibliografico
   InitMenus( '$$$', '07', PopupNivelBibliografico, cxNivelBibliograficoBtn );

end;

procedure TfrmCatalogacion.PopupFormaCatalogacionPopup(Sender: TObject);
begin

   // Abrir Tipo de Catalogacion
   InitMenus( '$$$', '18', PopupFormaCatalogacion, cxFormaCatalogBtn );

end;

procedure TfrmCatalogacion.PopupNivelCodificacionPopup(Sender: TObject);
begin

   // Abrir Nivel de Codificaci�n
   InitMenus( '$$$', '17', PopupNivelCodificacion, cxNivelCodificacionBtn );

end;

procedure TfrmCatalogacion.PopupTipoFechaEstPublicacionPopup(
  Sender: TObject);
begin

   // Abrir Tipo Fecha / Est de la Publicaci�n
   InitMenus( '008', '06', PopupTipoFechaEstPublicacion, cx008_TipoFechaEstPublicacion );

end;

procedure TfrmCatalogacion.PopupRegistroModificadoPopup(Sender: TObject);
begin

   // Abrir Registro Modificado
   InitMenus( '008', '38', PopupRegistroModificado, cx008_RegistroModificado );

end;


procedure TfrmCatalogacion.PopupFuenteCatalogPopup(Sender: TObject);
begin

   // Abrir Fuente de la Catalogaci�n
   InitMenus( '008', '39', PopupFuenteCatalog, cx008_FuenteCatalogacion );

end;


// Modifica Lugar de Publicaci�n
procedure TfrmCatalogacion.cx008_LugarPublicacionPropertiesButtonClick(
  Sender: TObject; AButtonIndex: Integer);
begin

   // Categor�a 64 del Tesauro
   frmSeleccionaValores.Reset( registroMarc21.RED );
   frmSeleccionaValores.InicializarInfo( 'Seleccionar C�digo', '008 Posici�n 15 a 17' );
   frmSeleccionaValores.InicializarValores( 'T64', cx008_LugarPublicacion.Text, true );

   if frmSeleccionaValores.ShowModal = mrYes then
   begin
      cx008_LugarPublicacion.Text := frmSeleccionaValores.cValorSeleccionado;

      cx008_LugarPublicacion.Hint := frmSeleccionaValores.cValorSeleccionado + '- '+
                                     frmSeleccionaValores.cDescripcionValorSeleccionado;
   end;

end;

// Modifica Idioma
procedure TfrmCatalogacion.cx008_IdiomaPropertiesButtonClick(
  Sender: TObject; AButtonIndex: Integer);
begin

   frmSeleccionaValores.Reset( registroMarc21.RED );
   frmSeleccionaValores.InicializarInfo( 'Seleccionar C�digo', '008 Posici�n 35 a 37' );
   frmSeleccionaValores.InicializarValores( 'T62', cx008_Idioma.Text, false );  // Categor�a 64 del Tesauro

   if frmSeleccionaValores.ShowModal = mrYes then
   begin
      cx008_Idioma.Text := frmSeleccionaValores.cValorSeleccionado;
      cx008_Idioma.Hint := frmSeleccionaValores.cValorSeleccionado + '- '+
                           frmSeleccionaValores.cDescripcionValorSeleccionado;
   end;

end;


// Resaltar� el elemento del Popup que ha sido seleccionado segun el codigo
procedure TfrmCatalogacion.HiliteItemSelected( cCodigo: string; popupMenu: TPopupMenu );
var
   i: integer;
   nPos: integer;
begin

   for i := 0 to popupMenu.Items.Count - 1 do
   begin
       popupMenu.Items[i].Checked := false;

       nPos := Pos( '-', popupMenu.Items[i].Caption );

       if nPos <> 0 then
       begin
          if Copy( popupMenu.Items[i].Caption, 1, Pos( '-', popupMenu.Items[i].Caption )-1 ) = cCodigo then
             popupMenu.Items[i].Checked := true;
       end;

   end;

end;


// Evento que se activa al hacer click en un elemento del pop up de Tipo de Registro
procedure TfrmCatalogacion.MenuItemTipoMaterial(Sender: TObject);
begin

   cxTipoMaterialBtn.Caption := TMenuItem( Sender ).Hint;
   cxTipoMaterialBtn.Hint    := TMenuItem( Sender ).Caption;
   HiliteItemSelected( cxTipoMaterialBtn.Caption, PopupTipoRegistro );

   registroMARC21.Encabezado_TipoRegistro := cxTipoMaterialBtn.Caption;
   
   //registroMARC21.ModificarValorCampo( '$$$', '06', cxTipoMaterialBtn.Caption )

   // PENDIENTE
   // Generar el segundo GROUP BOX con entradas espec�ficas
   // para el tipo de MATERIAL

end;

// Evento que se activa al hacer click en un elemento del pop up de Estado de Registro
procedure TfrmCatalogacion.MenuItemEdoRegistroClick(Sender: TObject);
begin

   cxEstadoRegistroBtn.Caption := TMenuItem( Sender ).Hint;
   cxEstadoRegistroBtn.Hint    := TMenuItem( Sender ).Caption;
   HiliteItemSelected( cxEstadoRegistroBtn.Caption, PopupEstadoRegistro );

   registroMARC21.Encabezado_EstadoRegistro := cxEstadoRegistroBtn.Caption;
   //registroMARC21.ModificarValorCampo( '$$$', '05', cxEstadoRegistroBtn.Caption );


end;

// Evento que se activa al hacer click en un elemento del pop up de Nivel Bibliogr�fico
procedure TfrmCatalogacion.MenuItemNivelBibliogClick(Sender: TObject);
begin

   cxNivelBibliograficoBtn.Caption := TMenuItem( Sender ).Hint;
   cxNivelBibliograficoBtn.Hint    := TMenuItem( Sender ).Caption;
   HiliteItemSelected( cxNivelBibliograficoBtn.Caption, PopupNivelBibliografico );

   registroMARC21.Encabezado_NivelBibliografico := cxNivelBibliograficoBtn.Caption;

end;

// Evento que se activa al hacer click en un elemento del pop up de Nivel de Codificaci�n
procedure TfrmCatalogacion.MenuItemNivelCodificacionClick(Sender: TObject);
begin

   cxNivelCodificacionBtn.Caption := TMenuItem( Sender ).Hint;
   cxNivelCodificacionBtn.Hint    := TMenuItem( Sender ).Caption;
   HiliteItemSelected( cxNivelCodificacionBtn.Caption, PopupNivelCodificacion );

   registroMARC21.Encabezado_NivelCodificacion := cxNivelCodificacionBtn.Caption;

end;

// Evento que se activa al hacer click en un elemento del pop up de Forma de Catalogaci�n
procedure TfrmCatalogacion.MenuItemFormaCatalogClick(Sender: TObject);
begin

   cxFormaCatalogBtn.Caption := TMenuItem( Sender ).Hint;
   cxFormaCatalogBtn.Hint    := TMenuItem( Sender ).Caption;
   HiliteItemSelected( cxFormaCatalogBtn.Caption, PopupFormaCatalogacion );

   registroMARC21.Encabezado_FormaCatalogacion := cxFormaCatalogBtn.Caption;

end;

// Evento que se activa al hacer click en un elemento del pop up de Tipo Fecha / Est de Publicacion
procedure TfrmCatalogacion.MenuItemTipoFechaEstPublicacionClick(Sender: TObject);
begin

   cx008_TipoFechaEstPublicacion.Caption := TMenuItem( Sender ).Hint;
   cx008_TipoFechaEstPublicacion.Hint    := TMenuItem( Sender ).Caption;
   HiliteItemSelected( cx008_TipoFechaEstPublicacion.Caption, PopupTipoFechaEstPublicacion );

   // PENDIENTE:  Generar modificacion a un elemento del CAMPO 008

end;

// Evento que se activa al hacer click en un elemento del pop up del men� Registro Modificado
procedure TfrmCatalogacion.MenuItemRegistroModificadoClick(Sender: TObject);
begin

   cx008_RegistroModificado.Caption := TMenuItem( Sender ).Hint;
   cx008_RegistroModificado.Hint    := TMenuItem( Sender ).Caption;
   HiliteItemSelected( cx008_RegistroModificado.Caption, PopupRegistroModificado );

   // PENDIENTE:  Generar modificacion a un elemento del CAMPO 008

end;

// Evento que se activa al hacer click en un elemento del pop up del men� Fuente de Catalogaci�n
procedure TfrmCatalogacion.MenuItemFuenteCatalogacionClick(Sender: TObject);
begin

   cx008_FuenteCatalogacion.Caption := TMenuItem( Sender ).Hint;
   cx008_FuenteCatalogacion.Hint    := TMenuItem( Sender ).Caption;
   HiliteItemSelected( cx008_FuenteCatalogacion.Caption, PopupFuenteCatalog );

   // PENDIENTE:  Generar modificacion a un elemento del CAMPO 008

end;



function TfrmCatalogacion.LocalizarBotones_DE_Indicadores( cID_DEL_Campo: string; nID: integer ): TCXButton;
var
    pos: integer;

begin

    Result := nil;

    pos := Ubicar_Campo_Posicion( cID_DEL_Campo );

    if pos <> -1 then
    begin
       // Se ubico el TPosicionObj correspondiente al ID del Campo
       Result := TPosicionObj( aWinControls.Items[pos] ).BuscarBotonAux(nil,nID);
       exit;
    end;

end;

//
// Genera la lista de elementos que pueden elegirse en una catalogaci�n
// en la parte de IDENTIFICADORES
//
procedure TfrmCatalogacion.Proc_Click_EN_IDENTIFICADOR(Sender: TObject);
var
  i: integer;
  objCampo: TMARC21_Campo;

  cIDCampo: string;

  formHeight: integer;
  formTop: integer;

  nMaxWidth: integer;

begin

    for i := 0 to aWinControls.Count - 1 do
    begin

        // Buscar boton por TObject=Sender
        if TPosicionObj( aWinControls.Items[i] ).BuscarBotonAux( TCXButton(Sender), 0 ) <> nil then
        begin
           // Se ubico el TCXButton

           PanelIndicadoresEditor.Caption := '';

           formTop := TCxButton(Sender).Top +
                      TCxButton(Sender).Height + 2;

           cIDCampo := TCxButton(Sender).Name;

           if Pos( 'AUXID', cIDCampo ) <> 0 then
               cIDCampo := Copy( cIDCampo, 8, 50 );

           objCampo := registroMARC21.BuscarCampoMARC_X_ID( cIDCampo );

           if objCampo <> nil then
           begin

              editandoIndicadoresCampo := objCampo;

              lblCampo.Caption := objCampo.cIDCampo + ' ' + objCampo.ObtenerDescripcion;

              Panel5.Visible := objCampo.objID1 <> nil;

              if objCampo.objID1 <> nil then
              begin
                 if objCampo.objID1.aValoresPosibles.Count = 0 then
                    objCampo.objID1.CargarValoresPosibles;

                 ColocarValoresIndicadores( objCampo, 1, objCampo.objID1.ObtenerDescripcion,
                    objCampo.objID1 );
              end;

              Panel6.Visible := objCampo.objID2 <> nil;

              if objCampo.objID2 <> nil then
              begin
                 Label9.Caption := '2- '+objCampo.objID2.ObtenerDescripcion;

                 if objCampo.objID2.aValoresPosibles.Count = 0 then
                    objCampo.objID2.CargarValoresPosibles;

                 ColocarValoresIndicadores( objCampo, 2, objCampo.objID2.ObtenerDescripcion,
                    objCampo.objID2 );
              end;

              formHeight := Panel4.Height;

              if Panel5.Visible then
                 formHeight := formHeight + Panel5.Height;

              if Panel6.Visible then
                 formHeight := formHeight + Panel6.Height;

              formHeight := formHeight + PanelPie.Height;

              PanelIndicadoresEditor.Height := formHeight;

              if formTop + formHeight > ScrollBox1.Height then
              begin
                 formTop := ScrollBox1.Height - formHeight - 15;

                 PanelIndicadoresEditor.Left := PanelIndicadoresEditor.Left +
                                                TCxButton(Sender).Width + 2;
              end
              else
                 PanelIndicadoresEditor.Left    := TCxButton(Sender).Left;

              PanelIndicadoresEditor.Top    := formTop;
              PanelIndicadoresEditor.Height := formHeight;

              // Verificar anchura maxima del Panel de Indicadores
              nMaxWidth := Panel5.Width;

              if Panel6.Width > nMaxWidth then
                 nMaxWidth := Panel6.Width;

              // Emparejar los Paneles de X1 y X2
              Panel5.Width := nMaxWidth;
              Panel6.Width := nMaxWidth;

              // ahora verificar
              if lblCampo.Canvas.TextWidth( lblCampo.Caption + ' ' + Label7.Caption ) + 50 > nMaxWidth then
                 nMaxWidth := lblCampo.Canvas.TextWidth( lblCampo.Caption + ' ' + Label7.Caption ) + 50;

              if nMaxWidth > PanelIndicadoresEditor.Width then
                 PanelIndicadoresEditor.Width := nMaxWidth;

              btnCloseIndicador.Left := PanelIndicadoresEditor.Width - btnCloseIndicador.Width - 10;

              // Mostrar el Panel
              PanelIndicadoresEditor.Visible := true;
              PanelIndicadoresEditor.BringToFront;

           end;
        end;

    end;

end;

//
// 20-mar-2009
//
procedure TfrmCatalogacion.Proc_Click_EN_AUXILIAR(Sender: TObject);
var
  i: integer;
  objCampo: TMARC21_Campo;
  objSubCampo: TMARC21_SubCampo;

  cUniqueID_Campo, cUniqueID_SubCampo: string;

  nPos: integer;

begin

  for i := 0 to aWinControls.Count - 1 do
  begin

      if TPosicionObj( aWinControls.Items[i] ).BuscarBotonAux( TCXButton(Sender), 0 ) <> nil then
      begin
         nPos := Pos( ':', TCXButton(Sender).Hint );

         cUniqueID_Campo    := '';
         cUniqueID_SubCampo := '';

         if nPos <> 0 then
         begin
            cUniqueID_Campo    := Copy( TCXButton(Sender).Hint, 1, nPos-1 );
            cUniqueID_SubCampo := Copy( TCXButton(Sender).Hint, nPos+1, 20 );
         end;

         if cUniqueID_Campo <> '' then
         begin

            objCampo    := registroMarc21.BuscarCampoMARC_X_ID( cUniqueID_Campo );
            objSubCampo := nil;

            if objCampo <> nil then
            begin
               objSubCampo := objCampo.BuscarSubCampo_X_ID( cUniqueID_SubCampo );

               if objSubCampo <> nil then
               begin
                  //
                  // El cTag contendr� el c�digo del tesauro
                  //
                  if (objSubCampo.cTag = '') and (objSubCampo.cTesauro='{MARC}') then
                      objSubCampo.cTag := objSubCampo.cTesauro;

                  if objSubCampo.cTag = '' then
                  begin
                     with Datos.dmDatos.qryAnySQL do
                     begin
                          // Pendiente: agregar Tesauro por RED DE BIBLIOTECAS
                          // para el modelo SaaS
                          //
                          SQL.Clear;
                          SQL.Add( 'SELECT ID_CATEGORIA FROM tesauro_categorias ' );
                          SQL.Add( 'WHERE ID_RED='+IntToStr(objSubCampo.Parent.Parent.RED)+' and DESCRIPCION="'+objSubCampo.cTesauro+'" ');
                          Open;

                          if not Eof then
                             objSubCampo.cTag := 'T'+FieldByName('ID_CATEGORIA').AsString;

                          Close;
                     end;
                  end;

                  frmSeleccionaValores.Reset( objSubCampo.Parent.Parent.RED );

                  if objSubCampo.cTag = '{MARC}' then
                  begin
                     frmSeleccionaValores.TesauroDECodigoMARC( objCampo.cIDCampo,
                                                               objSubCampo.cIDSubCampo );

                     frmSeleccionaValores.InicializarInfo( 'Seleccionar Valor para '+objSubCampo.cIDCampo+'/'+objSubCampo.cIDSubCampo+' '+objSubCampo.ObtenerDescripcion,
                                                           objSubCampo.ObtenerDescripcion );
                  end
                  else
                     frmSeleccionaValores.InicializarInfo( 'Seleccionar Valor en la tabla '+objSubCampo.cTesauro, objSubCampo.ObtenerDescripcion );

                  frmSeleccionaValores.InicializarValores( objSubCampo.cTag,
                      TPosicionObj( aWinControls.Items[i] ).pControl.Text, false );  // Categor�a 64 del Tesauro

                  if frmSeleccionaValores.ShowModal = mrYes then
                  begin
                     TPosicionObj( aWinControls.Items[i] ).pControl.Text := frmSeleccionaValores.cValorSeleccionado;
                     objSubCampo.Valor := frmSeleccionaValores.cValorSeleccionado;
                  end;

               end;

            end;

         end;

      end;

  end;

end;


procedure TfrmCatalogacion.ColocarValoresIndicadores( objCampo: TMarc21_Campo; nID: integer; cDescrip: string;
         objIndicadores: TMARC21_Identificador );
var
   nPos: integer;
   cCodigo: string;

   btnIndicador1: TCXButton;
   btnIndicador2: TCXButton;

   nMaxWidth: integer;

   procedure AgregarRangoValores_En_ComboBox( nID: integer; c_Codigo: string );
   var
     j: integer;
     xSaveIndex, xPos: integer;
     cDesde, cHasta: string;
   begin

     cDesde := c_Codigo;
     cHasta := c_Codigo;

     xPos := Pos( ':', c_Codigo );

     if xPos <> 0 then
     begin
        cDesde := trim(Copy( cDesde, 1, xPos-1 ));
        cHasta := trim(Copy( cHasta, xPos+1, 50 ));
     end;

     if cDesde = '' then cDesde := '0';
     if cHasta = '' then cHasta := '0';

     xSaveIndex := 0;

     for j := StrToInt(cDesde) to StrToInt(cHasta) do
     begin
        if nID = 1 then
        begin
           cxComboBox1.Properties.Items.Add( IntToStr(j) );

           if IntToStr(j) = btnIndicador1.Caption then
              xSaveIndex := j;

        end
        else if nID = 2 then
        begin
           cxComboBox2.Properties.Items.Add( IntToStr(j) );

           if IntToStr(j) = btnIndicador2.Caption then
              xSaveIndex := j;

        end;
     end;

     if nID = 1 then
        cxComboBox1.ItemIndex := xSaveIndex
     else if nID = 2 then
        cxComboBox2.ItemIndex := xSaveIndex;

   end;

   // Coloca Radio Buttons y
   // Devuelve la longitud (width) m�xima de los items               
   function CrearElementosRadio( radioGroup1: TcxRadioGroup; nID1: integer ): integer;
   var
      xSaveIndex: integer;

      radItem: TCxRadioGroupItem;

      j: integer;

   begin

      Result := 0;

      radioGroup1.Left  := 8;
      radioGroup1.Width   := PanelIndicadoresEditor.Width - 20;

      radioGroup1.ItemIndex := -1;
      radioGroup1.Visible := True;
      radioGroup1.Properties.Items.Clear;

      xSaveIndex := 0;

      for j := 0 to objIndicadores.aValoresPosibles.Count - 1 do
      begin
         radItem := TCxRadioGroupItem(radioGroup1.Properties.Items.Add);
         radItem.Caption := objIndicadores.aValoresPosibles.Strings[j];

         if radioGroup1.Canvas.TextWidth( radItem.Caption ) + 40 > Result then
            Result := radioGroup1.Canvas.TextWidth( radItem.Caption ) + 40;

         cCodigo := objIndicadores.aValoresPosibles.Strings[j];

         nPos := Pos( '-', cCodigo );
         if nPos <> 0 then cCodigo := trim(Copy( cCodigo, 1, nPos-1 ));

         if nID=1 then
         begin
            if cCodigo = btnIndicador1.Caption then xSaveIndex := j;
         end
         else
         begin
            if cCodigo = btnIndicador2.Caption then xSaveIndex := j;
         end;

      end;

      radioGroup1.ItemIndex := xSaveIndex;

      radioGroup1.Height := (objIndicadores.aValoresPosibles.Count+1) * 20;

   end;

begin

    btnIndicador1 := nil;
    btnIndicador2 := nil;

    if nID = 1 then btnIndicador1 := LocalizarBotones_DE_Indicadores( objCampo.ID, 1 );
    if nID = 2 then btnIndicador2 := LocalizarBotones_DE_Indicadores( objCampo.ID, 2 );

    if objIndicadores.aValoresPosibles.Count = 1 then
    begin
       // solo un indicador posible
       cCodigo := objIndicadores.aValoresPosibles[0];

       nPos := Pos( '-', cCodigo );
       if nPos <> 0 then
          cCodigo := trim(Copy( cCodigo, 1, nPos-1 ));

       if nID = 1 then
       begin
          Label8.Visible := true;
          cxRadioGroup1.Visible := false;
          cxComboBox1.Visible   := false;
          Label8.Caption := '1 - '+cDescrip;

          if Pos( ':', cCodigo ) <> 0 then
          begin
             cxComboBox1.Visible := true;
             cxComboBox1.Properties.Items.Clear;

             AgregarRangoValores_En_ComboBox( 1, cCodigo );
          end
          else
          begin
             Label8.Visible := false;

             cxRadioGroup1.Caption := '1 - '+cDescrip;

             CrearElementosRadio(cxRadioGroup1, 1);
             Panel5.Height := cxRadioGroup1.Height + 15;
          end;

       end
       else if nID=2 then
       begin
          Label9.Visible := true;
          cxRadioGroup2.Visible := false;
          cxComboBox2.Visible   := false;
          Label9.Caption := '2 - '+cDescrip;

          if Pos( ':', cCodigo ) <> 0 then
          begin
             cxComboBox2.Visible := true;
             cxComboBox2.Properties.Items.Clear;

             AgregarRangoValores_En_ComboBox( 2, cCodigo );
          end
          else
          begin
             Label9.Visible := false;

             cxRadioGroup2.Caption := '1 - '+cDescrip;

             CrearElementosRadio(cxRadioGroup2, 2);
             Panel6.Height := cxRadioGroup2.Height + 15;
          end;

       end;

    end
    else
    begin
       // Radio Group
       if nID = 1 then
       begin
          Label8.Visible := false;
          cxComboBox1.Visible := false;

          cxRadioGroup1.Caption := '1 - '+cDescrip;

          nMaxWidth := CrearElementosRadio(cxRadioGroup1, 1);

          Panel5.Width := nMaxWidth + 30;
          Panel5.Height := cxRadioGroup1.Height + 15;
       end
       else
       begin
          Label9.Visible := false;
          cxComboBox2.Visible := false;

          cxRadioGroup2.Caption := '2 - '+cDescrip;

          nMaxWidth := CrearElementosRadio(cxRadioGroup2, 2);

          Panel6.Width := nMaxWidth + 30;
          Panel6.Height := cxRadioGroup2.Height + 15;
       end;

    end;

 end;

procedure TfrmCatalogacion.btnCloseIndicadorClick(Sender: TObject);
begin
   PanelIndicadoresEditor.Visible := false;
end;



procedure TfrmCatalogacion.btnSaveClick(Sender: TObject);
var
   i: integer;
   objCampo: TMARC21_Campo;

   function VerificarCamposGenerales: boolean;
   begin

      if registroMARC21.BuscarCampoMARC( '$$$' ) = nil then
      begin
         result := false;
         Self.DisplayWarningMessage( 'no hay campo header', 'Aviso' );
      end;

   end;

begin

   if not VerificarCamposGenerales then
      exit;

   if MessageBox( Self.Handle, '� Desea aplicar los cambios ?', 'Aviso', MB_YESNO + MB_ICONQUESTION ) = IDYES then
   begin

      dmDatos.mDataBase1.BeginTrans;

      try

          // saber el ID del TITULO

          if not Editing then
          begin

             registroMARC21.AgregarRegistro;
             registroMARC21.AgregarDigitalizacion( Image1, Image2 );

             lblNoControl.Caption := FloatToStr( registroMARC21.nIDTitulo );
             lblFechaCreacion.Caption := DateToStr( registroMARC21.dFechaIngresoRegistro );

          end;

          // Buscar Campo Cabecera

          // Buscar Campo General

          // Ir campo por campo
          for i := 0 to registroMARC21.ContarCampos - 1 do
          begin

             objCampo := registroMARC21.ObtenerCampoMARC(i);

             if (objCampo.bEncabezado) then
             begin
                registroMARC21.GuardarElementosEncabezado;
                continue;
             end;

             if objCampo.bDirectorio then continue;

             // otros campos
             registroMARC21.GuardarElementosSubCampos( objCampo );

          end;

          dmDatos.mDataBase1.CommitTrans; // .Commit;

          Self.DisplayInfoMessage( 'Se ha registrado el material', 'Aviso' );

      except

          dmDatos.mDataBase1.RollbackTrans; // .Rollback;

          raise;

      end;

   end;

end;

// Permite guardar cambios a los
// Identificadores
procedure TfrmCatalogacion.btnSaveIDsClick(Sender: TObject);
var
   cValor: string;
   nPos: integer;

   cxtheButton: TCXButton;
begin

   if editandoIndicadoresCampo <> nil then
   begin

      // verificar si hay Indicador # 1
      if editandoIndicadoresCampo.objID1 <> nil then
      begin

         cValor := '';

         if cxRadioGroup1.Visible then
            cValor := cxRadioGroup1.Properties.Items[ cxRadioGroup1.ItemIndex ].Caption
         else if cxComboBox1.Visible then
            cValor := cxComboBox1.Properties.Items[ cxComboBox1.ItemIndex ];

         nPos := Pos( '-', cValor );
         if nPos <> 0 then cValor := Copy( cValor, 1, nPos-1 );

         editandoIndicadoresCampo.objID1.Valor := cValor;

         cxTheButton := LocalizarBotones_DE_Indicadores(  editandoIndicadoresCampo.ID, 1 );

         if cxTheButton <> nil then
            cxTheButton.Caption := cValor;

      end;

      // verificar si hay Indicador # 1
      if editandoIndicadoresCampo.objID2 <> nil then
      begin

         cValor := '';

         if cxRadioGroup2.Visible then
            cValor := cxRadioGroup2.Properties.Items[ cxRadioGroup2.ItemIndex ].Caption
         else if cxComboBox2.Visible then
            cValor := cxComboBox2.Properties.Items[ cxComboBox2.ItemIndex ];

         nPos := Pos( '-', cValor );
         if nPos <> 0 then cValor := Copy( cValor, 1, nPos-1 );

         editandoIndicadoresCampo.objID2.Valor := cValor;

         cxTheButton := LocalizarBotones_DE_Indicadores(  editandoIndicadoresCampo.ID, 2 );

         if cxTheButton <> nil then
            cxTheButton.Caption := cValor;

      end;

   end;

   PanelIndicadoresEditor.Visible := false;

end;

procedure TfrmCatalogacion.btnCancelClick(Sender: TObject);
begin
   Close;
end;

procedure TfrmCatalogacion.cxTextEdit1PropertiesValidate(Sender: TObject;
  var DisplayValue: Variant; var ErrorText: TCaption; var Error: Boolean);
begin
  Error := false;
end;

procedure TfrmCatalogacion.btnAgregarCampoClick(Sender: TObject);
var
  i: integer;
begin
  // Agregar un campo MARC
  frmSeleccionaCampo.objetoMARC_EnUSO := registroMarc21;

  frmSeleccionaCampo.Init;

  if frmSeleccionaCampo.ShowModal = mrYes then
  begin
     // Agregar los campos

     for i := 0 to frmSeleccionaCampo.aCampos.Count - 1 do
         registroMarc21.AgregarCampo( frmSeleccionaCampo.aCampos[i], true, false );

     LimpiarElementos( false );
     CrearElementos( '', false );

     if frmSeleccionaCampo.aCampos.Count > 0 then
     begin

        // ubicar primer campo agregado para enfocar el FOCUS
        for i := 0 to aWinControls.Count - 1 do
        begin
            if TPosicionObj(aWinControls[i]).objCampo.cIDCampo = frmSeleccionaCampo.aCampos[0] then
            begin
               TPosicionObj(aWinControls[i]).pControl.SetFocus;
               break;
            end;
        end;

     end;

  end;

end;

procedure TfrmCatalogacion.popupInfoCampoPopup(Sender: TObject);
var
  objCampo: TMARC21_Campo;
begin

  // Conservar� la ID del campo
  AgregarSubCampo1.Hint := TLabel(TPopupMenu(Sender).popupComponent).Hint;

  objCampo := registroMARC21.BuscarCampoMARC_X_ID( AgregarSubCampo1.Hint );

  if objCampo.cUrl = '' then
     objCampo.Obtener_URL_Nota_DesdeDB;

  if objCampo.cUrl <> '' then
     VerDocumentacin1.Caption := 'Ver documentaci�n: '+objCampo.cUrl;

  RepetirCampo1.Hint := '';
  RepetirCampo1.Visible := objCampo.cRepetible_SN = 'S';

  if RepetirCampo1.Visible then
     RepetirCampo1.Hint := objCampo.cIDCampo;

end;

//
// Se ejecuta al hacer clic en un campo
//
procedure TfrmCatalogacion.GESURL_Label_Campo_Click(Sender: TObject);
var
  objCampo: TMARC21_Campo;
  cIDCampo: string;

  cText: string;

begin

  cIDCampo := TLabel(Sender).Caption;

  objCampo := registroMARC21.BuscarCampoMARC( cIDCampo );

  if objCampo.cUrl = '' then
     objCampo.Obtener_URL_Nota_DesdeDB;

  if objCampo.cNota = '' then
     objCampo.Obtener_URL_Nota_DesdeDB;

  cText := objCampo.cUrl;

  if objCampo.cNota <> '' then
     cText := cText + #13#10#13#10 + objCampo.cNota;

  if cText = '' then
     cText := 'No hay informaci�n adicional por mostrar';

  ALHintBalloonControl1.ShowTextHintBalloon( bmtInfo, objCampo.cIDCampo+' '+objCampo.ObtenerDescripcion, cText, 400, 10, 10, TLabel(Sender), bapBottomRight);

end;

//
// Se ejecuta al hacer clic el link de un subcampo
//

procedure TfrmCatalogacion.GESURL_Label_SubCampo_Click(Sender: TObject);
var
  objCampo: TMARC21_Campo;
  objSubCampo: TMARC21_SubCampo;
  cUniqueID_DEL_Campo: string;

  cText: string;

begin

  cUniqueID_DEL_Campo := TLabel(Sender).Hint;

  objCampo := registroMARC21.BuscarCampoMARC_X_ID( cUniqueID_DEL_Campo );
  objSubCampo := objCampo.BuscarSubCampo( TLabel(Sender).Caption );

  if objSubCampo <> nil then
  begin

     if objCampo.cUrl = '' then
        objCampo.Obtener_URL_Nota_DesdeDB;

     if objSubCampo.cNota = '' then
        objSubCampo.Obtener_URL_Nota_DesdeDB;

     cText := objSubCampo.cNota;

     if objCampo.cNota <> '' then
        cText := cText + #13#10#13#10 + objCampo.cNota;

     if objSubCampo.cTesauro <> '' then
        cText := cText + #13#10#13#10 + 'Tesauro: '+objSubCampo.cTesauro;

     if cText = '' then
        cText := 'No hay informaci�n adicional por mostrar';

     ALHintBalloonControl1.ShowTextHintBalloon( bmtInfo, 'Nota de SubCampo', cText, 400, 10, 10, TLabel(Sender), bapBottomRight);

  end;

end;


procedure TfrmCatalogacion.AgregarSubCampo1Click(Sender: TObject);
var
   i: integer;
begin

   frmSeleccionaSubCampo.campoMARC := registroMARC21.BuscarCampoMARC_X_ID( AgregarSubCampo1.Hint );
   frmSeleccionaSubCampo.Init;

   if frmSeleccionaSubCampo.ShowModal = mrYes then
   begin

     LimpiarElementos( false );
     CrearElementos( '', false );

     if frmSeleccionaSubCampo.primerSubCampoAgregado <> nil then
     begin

        // ubicar primer campo agregado para enfocar el FOCUS
        for i := 0 to aWinControls.Count - 1 do
        begin
            if (TPosicionObj(aWinControls[i]).objCampo.ID = frmSeleccionaSubCampo.campoMARC.ID) and
               (TPosicionObj(aWinControls[i]).cIDSubCampo = frmSeleccionaSubCampo.primerSubCampoAgregado.cIDSubCampo) then
            begin
               TPosicionObj(aWinControls[i]).pControl.SetFocus;
               break;
            end;
        end;

     end;

   end;

end;

procedure ConvertToJPEG( Image: TImage );
var
   Jpeg, JpegTemp: TJpegImage;
   nMaxWidth: integer;

   procedure ResizeJpeg( srcJpeg, destJpeg: TJpegImage; const MaxSize: integer );
   var
       Bitmap: TBitmap;
       Ratio: double;
   begin

       {Si l'image est vide ou la largeur est nulle, sortir}
       if (srcJpeg.Empty) or (MaxSize <= 0) then
          Exit;

       { Cr�ation d'un TBitmap le temps de redimensionner l'image }
       Bitmap := TBitmap.Create;
       try
           { D�termination du cadrage Vertical ou horizontal}
           Ratio := srcJpeg.Height / srcJpeg.Width;

           if Ratio > 1 then
           begin
              //Cadrage vertical
              Bitmap.Width := Round(MaxSize / Ratio);

              // 19jun2008
              if Bitmap.Width > Image.Picture.Bitmap.Width then
                 Bitmap.Width := Image.Picture.Bitmap.Width;
           end
           else
              //cadrage horizontal
              Bitmap.Width := MaxSize;

           Bitmap.Height := Round(Bitmap.Width * Ratio);

           //Copie de l'image avec redimensionnement sur le canvas du TBitmap
           Bitmap.Canvas.StretchDraw(Bitmap.Canvas.ClipRect, srcJpeg);

           {Il ne reste plus qu'� copier l'image redimensionn�e dan le Jpeg � retourner}
           destJpeg.Assign(Bitmap);

       finally
           Bitmap.Free;

       end;
   end;


begin

   if Image.Picture <> nil then
   begin
      JpegTemp := TJpegImage.Create;

      try
         Jpeg := TJpegImage.Create;

         try
             // cargar la imagen
             try
                Jpeg.Assign( Image.Picture );
             except
                Jpeg.Assign( Image.Picture.Bitmap );
             end;

             if JPEG_Quality = 2 then
             begin
                nMaxWidth := 250;
                Jpeg.CompressionQuality := 90;  //
             end
             else if JPEG_Quality = 5 then
             begin
                nMaxWidth := 350;
                Jpeg.CompressionQuality := 60;
             end
             else if JPEG_Quality = 10 then
             begin
                nMaxWidth := 450;
                Jpeg.CompressionQuality := 10;
             end;

             // redimensionarla
             ResizeJpeg( Jpeg, JpegTemp, nMaxWidth );

             // l'affecter au contr�le visuel
             Image.Picture.Assign(JpegTemp);
             Image.Update;
             Application.ProcessMessages;

         finally
             Jpeg.Free;
         end;

      finally
         JpegTemp.Free;

      end;

   end;

end;

procedure TfrmCatalogacion.btnCopyPortadaClick(Sender: TObject);
begin
  Clipboard.Assign(Image1.Picture);
end;


procedure TfrmCatalogacion.btmPastePortadaClick(Sender: TObject);
begin
  if Clipboard.HasFormat(CF_BITMAP) then
  begin
    Image1.Picture.Bitmap.Assign( Clipboard );
    ConvertToJPEG(Image1);
  end;
 end;

procedure TfrmCatalogacion.btnCopyPortadaFromFileClick(Sender: TObject);
var
  apImage: TImage;
begin

  If OpenPictureDialog1.Execute Then
  begin

     if TEncarta(Sender).Name = 'btnCopyPortadaFromFile'     then apImage := Image1
     else if TEncarta(Sender).Name = 'btnCopyContraFromFile' then apImage := Image2;

     apImage.Picture.LoadFromFile( OpenPictureDialog1.FileName );

     ConvertToJPEG( apImage );

     //AssignGraphicToField;

  end;

  Self.BringToFront;
end;



procedure TfrmCatalogacion.btnCopyContraClick(Sender: TObject);
begin
  Clipboard.Assign(Image2.Picture);
end;

procedure TfrmCatalogacion.btnPasteContraClick(Sender: TObject);
begin
  if Clipboard.HasFormat(CF_BITMAP) then
  begin
    Image2.Picture.Bitmap.Assign( Clipboard );
    ConvertToJPEG( Image2 );
  end;
end;

//
// 25-mar-2009
// Repetir Campo
//
procedure TfrmCatalogacion.RepetirCampo1Click(Sender: TObject);
var
  ndx, i: integer;
begin

  if (RepetirCampo1.Hint <> '') and (RepetirCampo1.Visible) then
  begin

     registroMarc21.AgregarCampo( RepetirCampo1.Hint, true, true );
     registroMarc21.ReasignarUniqueIDs;

     LimpiarElementos( false );
     CrearElementos( '', false );

     ndx := -1;

     // ubicar la �ltima ocurrencia del campo para enviarle el FOCUS
     for i := 0 to aWinControls.Count - 1 do
         if TPosicionObj(aWinControls[i]).objCampo.cIDCampo = RepetirCampo1.Hint then
            ndx := i;

     if ndx <> -1 then
        TPosicionObj(aWinControls[ndx]).pControl.SetFocus;

  end;

end;

// agregar una ocurrencia del mismo subcampo
procedure TfrmCatalogacion.btnAgregarOcurrenciaClick(Sender: TObject);
var
   objCampo : TMARC21_Campo;
   objSubCampo: TMARC21_SubCampo;

   objPosicion: TPosicionObj;

   cPatch: string;

   i, ndx: integer;

begin

  if ultimoEditActivado <> nil then
      if UbicarCampoYSubCampo( ultimoEditActivado, objCampo, objSubCampo, objPosicion ) then
      begin
         if objSubCampo.cRepetible_SN = 'S' then
         begin

             cPatch := objSubCampo.Descripcion_ESP;

             if objSubCampo.cRepetible_SN = 'S' then
                cPatch := cPatch + ' (R)';

            objCampo.AgregarSubCampo( objSubCampo.cIDSubCampo, cPatch, objSubCampo.Descripcion_ENG, '', objSubCampo.cTesauro,
                 objSubCampo.cConectorAACR, true );

            LimpiarElementos( false );
            CrearElementos( '', false );

            ndx := -1;

            // ubicar la ultima ocurrencia para enfocar el FOCUS
            for i := 0 to aWinControls.Count - 1 do
                if (TPosicionObj(aWinControls[i]).objCampo.ID = objCampo.ID) and
                   (TPosicionObj(aWinControls[i]).cIDSubCampo = objSubCampo.cIDSubCampo) then
                   ndx := i;

            if ndx <> -1 then
               TPosicionObj(aWinControls[ndx]).pControl.SetFocus;

         end;
      end;

end;

// Permite eliminar una ocurrencia / subcampo
procedure TfrmCatalogacion.btnEliminarOcurrenciaClick(Sender: TObject);
var
   objCampo : TMARC21_Campo;
   objSubCampo: TMARC21_SubCampo;

   objPosicion: TPosicionObj;

   pos: integer;

begin
  //
  if ultimoEditActivado <> nil then
      if UbicarCampoYSubCampo( ultimoEditActivado, objCampo, objSubCampo, objPosicion ) then
         if objSubCampo.cRepetible_SN = 'S' then
            if objCampo.EliminarOcurrencia( objSubCampo ) then
            begin
               pos := Ubicar_Indice_Posicion( objPosicion );

               ReUbicarControles( ultimoEditActivado, 0 );

               registroMarc21.EliminarSubCampo( objCampo, objSubCampo );

               objPosicion.LiberarControl;  // destruir control relacionado
               objPosicion.Free;

               if pos <> -1 then
                  aWinControls.Delete( pos );

               if pos-1 > 0 then
                  TWinControl(TPosicionObj( aWinControls.Items[pos-1] ).pControl).SetFocus;

               //LimpiarElementos( false );
               //CrearElementos( '', false );

            end;

end;

end.
