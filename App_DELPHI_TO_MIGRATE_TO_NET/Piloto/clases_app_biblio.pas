(*******
  Historial de Cambios:

  CLASES MARC

   01-feb-2009: Se inicia.
   10-feb-2009: Se crea la clase TMarc21_Cabecera.
   11-feb-2009: Se complementa la clase TMarc21_Identificador.
   18-feb-2009: Se agrega metodo ObtenerDescripcionElementoTesauro
                Se agregan eventos a TMARC21_Registro para guardar informacion
   12-mar-2009: Se agrega la función InicializarRegistroMARC21_DesdeBD_Titulo
   13-mar-2009: Se implementa propiedad cConectorAACR en TSubCampo
   26-mar-2009: Manejo de importacion de registros MARC desde fuentes externas

  PENDIENTE

    19-feb-2009: QUE las funciones ObtenerDescripcion puedan lidiar
                 con el IDIOMA

 **)

unit clases_app_biblio;

//{$DEFINE MODBC}

interface

uses SysUtils, Graphics, messages, Classes, forms, controls, extctrls, Contnrs, db {$IFDEF MODBC},mQuery{$ELSE},ADODB{$ENDIF};

const

  PUNTUACION_AACR = '/;:.,';

type

  TMarcError = class(Exception)
  end;

  TMARC21_Campo = class;

  TMARC21_SubCampo = class(TObject)
    private
     cValor: string;

     cID: string;

     cDescripcion_ESP: string;
     cDescripcion_ENG: string;

    public
     cIDCampo: string;
     cIDSubCampo: string;

     cUrl: string;
     cNota: string;

     bObsoleto: boolean;

     //cValorDefault: string;
     cRepetible_SN: string;

     cTesauro: string;
     cConectorAACR: string;

     cTag: string;

     Parent: TMARC21_Campo;

     constructor Create( id_campo, id_subcampo: string;
        c_DescripESP, c_DescripENG, c_ValorDefault, c_Tesauro, c_ConectorAACR: string;
        pParent: TMARC21_Campo );

     function ObtenerDescripcion: string;
     function Obtener_URL_Nota_DesdeDB: boolean;

     function ObtenerID: string;

    published
     property ID: string read cID write cID;

     property Valor: string read cValor write cValor;

     property Descripcion_ESP: string read cDescripcion_ESP write cDescripcion_ESP;
     property Descripcion_ENG: string read cDescripcion_ENG write cDescripcion_ENG;

  end;

  TMARC21_Identificador = class(TObject)
   private
     cValor: string;

     cDescripcion_ESP: string;
     cDescripcion_ENG: string;

   public
     cIDCampo: string;
     nID: integer; // 1 o 2

     aValoresPosibles: TStringList;

     Parent: TMARC21_Campo;

     constructor Create( id_campo: string; id: integer; c_DescripESP, c_DescripENG, c_ValorDefault: string );
     destructor Destroy; override;

     procedure AgregarValorPosible( cSubCodigo: string; cDescripESP, cDescripENG: string ); // 11feb2009
     procedure CargarValoresPosibles;

     function ObtenerDescripcion: string;

   published
     property Valor: string read cValor write cValor;

  end;

(*  TMARC21_ElementoCabecera = class(TObject)
   private
     cIDCampo: string;
     cDato: string;

     cDescripcion_ESP: string;
     cDescripcion_ENG: string;

     aValoresPosibles: TStringList;

     cValor: string;

   public
     cPosiciones: string;

     constructor Create;
     destructor Destroy; override;

     function ObtenerDescripcion: string;

   published
     property Valor: string read cValor write cValor;

  end; *)

  TMARC21_Registro = class;

  TMARC21_Campo = class(TObject)
   private
     cID: string;

     cValor: string; // algunos campos llevan un valor asignado directamente

     cDescripcion_ESP: string;
     cDescripcion_ENG: string;

     ElementosEncabezado: TObjectList;
     SubCampos: TObjectList;

   protected
     procedure SetValor( cNewValue: string );
     function GetValor: string;

   public
     cIDCampo: string;

     cUrl: string;
     cNota: string;

     bEncabezado: boolean;
     bDirectorio: boolean;

     bObsoleto: boolean;

     bAutomatico: boolean;

     cRepetible_SN: string;

     Parent: TMARC21_Registro;

     objID1: TMARC21_Identificador;
     objID2: TMARC21_Identificador;

     bSinSubCampos: boolean;  // 26MAR2009

     constructor Create( id_campo: string; pParent: TMARC21_Registro; c_DescripESP, c_DescripENG: string );
     destructor Destroy; override;

     function CargarDefinicion: boolean;

     procedure ActualizaRepetible;

     function ObtenerDescripcion: string;

//     procedure AgregarElementoDeCabecera( cDato: String; cDescripESP, cDescripENG, cValorDefault: string ); // 10feb2009

     procedure AgregarIdentificador( nID: integer; cDescripESP, cDescripENG, cValorDefault: string );
     procedure AgregarSubCampo( cID: string; cDescripESP, cDescripENG: string; cValorDefault, cTesauro, cConectorAACR: string; bForzarDuplicado: boolean=false );

     procedure AgregarSubCampos_DesdeCadenaMARC( cStr: string; cDelimitador: string = '$' );

     function ContarSubCampos: integer;
     function SubCampo( idx: integer ): TMARC21_SubCampo;
     function BuscarSubCampo( cDato: string ): TMARC21_SubCampo;
     function BuscarSubCampo_X_ID( cID: string ): TMARC21_SubCampo;
     function ObtenerIndexSubCampo( objSubCampo: TMARC21_SubCampo ): integer;

     function ContarOcurrencias( cSubCampo: string ): integer; // 25mar2009
     function EliminarOcurrencia( objSubCampo: TMARC21_SubCampo ): boolean;

     //function ContarElementosCabecera: integer;
     //function ElementoCabecera( idx: integer ): TMARC21_ElementoCabecera;
     //function BuscarElementoCabecera( cDato: string ): TMARC21_ElementoCabecera;

     function Obtener_URL_Nota_DesdeDB: boolean;

     function ConcatenarValores(concatenarConEspacios: boolean=true; incluirIDSubCampos: boolean = false): string;

     function ObtenerID: string;

     //function ObtenerValorDePosicionFija( cPosicion: string ): string;

   published

     property ID: string read cID write cID;
     property Valor: string read GetValor write SetValor;

  end;

  TMARC21_Registro = class(TObject)
   private
      nIDRed: integer;

      aCamposMarc: TStringList;

      // Datos del LEADER / Encabezado
      FLongitudRegistro: integer;
      FEstadoRegistro: string;
      FTipoRegistro: string;
      FNivelBibliografico: string;
      FTipoControl:        string;
      FCodigoEsquemaChars: string;
      FConteoIndicadores:    integer;
      FConteoCodigoSubcampo: integer;
      FDireccionBase_Datos:  integer;
      FNivelCodificacion: string;
      FFormaCatalogacion: string;
      FNivelRegistro_Recurso: string;
      FMapaEntradas:          string;

      {$IFDEF MODBC}
      pQuery: TmQuery;
      {$ELSE}
      pQuery: TADOQuery;
      {$ENDIF}

      procedure CargarConfig( nIDBiblioteca: integer );

   protected
      // Leader procedures
      procedure SetLongitudRegistro( newValue: integer );
      procedure SetEstadoRegistro( newValue: string );
      procedure SetTipoRegistro( newValue: string );
      procedure SetNivelBibliografico( newValue: string );
      procedure SetTipoControl( newValue: string );
      procedure SetCodigoEsquemaChars( newValue: string );

      procedure SetConteoIndicadores( newValue: integer );
      procedure SetConteoCodigoSubcampo( newValue: integer );
      procedure SetDireccionBase_Datos( newValue: integer );

      procedure SetNivelCodificacion( newValue: string );
      procedure SetFormaCatalogacion( newValue: string );
      procedure SetNivelRegistro_Recurso( newValue: string );
      procedure SetMapaEntradas( newValue: string );

   public
      Edit: boolean;

      dFechaIngresoRegistro: TDateTime;
      nIDTitulo: double;

      bConfigInit: boolean;
      bAgregar_Puntuacion_Automatica: boolean;  //13mar2009

      //
      // Datos del campo 008 {TODOS LOS MATERIALES}
      // propiedades Campo 008 que aplican para todos los materiales
      // hay otras propiedades del mismo campo que
      // solo aplican para ciertos materiales según
      // lo que haya en el campo 006:00
      //
      F008_FechaIngresoRegistro: string;
      F008_TipoFechaEstadoPub: string;
      F008_Fecha_1: string;
      F008_Fecha_2: string;
      F008_LugarPublicacion: string;
      F008_Idioma: string;
      F008_RegistroModificado: string;
      F008_FuenteCatalogacion: string;

      constructor Create;
      destructor Destroy; override;

      procedure Clear;

      procedure InicializarRegistroMARC21_DesdePlantilla( nBiblioteca, nPlantilla: integer );
      procedure InicializarRegistroMARC21_DesdeBD_Titulo( nBiblioteca: integer; nIDTitulo: double  );
      procedure InicializarRegistroXRango( cRango: string; bSoloDefinicionesDeCampo: boolean );

      function AgregarCampo( cIDCodigo: string; bAgregarDefaults, bPermitirDuplicar: boolean ): TMARC21_Campo ;

      function ContarCampos: integer;
      function ObtenerCampoMARC( idx: integer ) : TMARC21_Campo;

      function BuscarCampoMARC( cCampoXBuscar: string ) : TMARC21_Campo;
      function BuscarCampoMARC_X_ID( cID: string ): TMARC21_Campo;

      function ObtenerIndexCampo( objCampo: TMARC21_Campo ): integer;

      function ObtenerDescripcionElementoTesauro( cCategoria, cIDValor: string ): string;
      function ObtenerDescripcionSubcodigo( cCampo, cSubCampo, cCodigo: string ): string;

      function ModificarValorCampo( cIDCampo, cSubCodigo, cNuevoValor: string ): boolean;
      function ObtenerValorCampo( cIDCampo, cCodigo, cSubCodigo: string ) : string;

      procedure AgregarActualizarCatalogacion_Valor( objCampo: TMARC21_Campo; cCodigo, cSubCodigo: string );

      function AgregarRegistro: boolean;

      function IniciarGuardado: boolean;
      function FinalizarGuardado: boolean;

      function AgregarDigitalizacion( var apImage1: TImage; var apImage2: TImage ): boolean;


      procedure GuardarElementosEncabezado;
      procedure GuardarElementosSubCampos( objCampo: TMARC21_Campo );

      function EliminarCampo( objCampo: TMARC21_Campo ): boolean;
      function EliminarSubCampo( objCampo: TMARC21_Campo; objSubCampo: TMARC21_SubCampo ): boolean;

      procedure ReasignarUniqueIDs;

      property RED: integer read nIDRed;

      // metodos publicos de manejo de registros MARC
      procedure InicializarCabecera( cValue: string );
      procedure GenerarCabecera;

      {$IFDEF MODBC}
      property Query: TmQuery read pQuery write pQuery;
      {$ELSE}
      property Query: TADOQuery read pQuery write pQuery;
      {$ENDIF}

      // propiedades del Encabezado
      property Encabezado_Longitud: integer             read FLongitudRegistro      write SetLongitudRegistro;
      property Encabezado_EstadoRegistro: string        read FEstadoRegistro        write SetEstadoRegistro;
      property Encabezado_TipoRegistro:   string        read FTipoRegistro          write SetTipoRegistro;
      property Encabezado_NivelBibliografico: string    read FNivelBibliografico    write SetNivelBibliografico;
      property Encabezado_TipoControl: string           read FTipoControl           write SetTipoControl;
      property Encabezado_CodigoEsquemaChars: string    read FCodigoEsquemaChars    write SetCodigoEsquemaChars;
      property Encabezado_ConteoIndicadores: integer    read FConteoIndicadores     write SetConteoIndicadores;
      property Encabezado_ConteoCodigoSubcampo: integer read FConteoCodigoSubcampo  write SetConteoCodigoSubcampo;
      property Encabezado_DireccionBase_Datos: integer  read FDireccionBase_Datos   write SetDireccionBase_Datos;
      property Encabezado_NivelCodificacion: string     read FNivelCodificacion     write SetNivelCodificacion ;
      property Encabezado_FormaCatalogacion: string     read FFormaCatalogacion     write SetFormaCatalogacion ;
      property Encabezado_NivelRegistro_Recurso: string read FNivelRegistro_Recurso write SetNivelRegistro_Recurso ;
      property Encabezado_MapaEntradas: string          read FMapaEntradas          write SetMapaEntradas;

  end;

  function ObtenerVarChar( pField: TField ): string;

implementation

uses Unit1, Datos, xpPages, xpCombo;


constructor TMARC21_SubCampo.Create( id_campo, id_subcampo: string;
   c_DescripESP, c_DescripENG, c_ValorDefault, c_Tesauro, c_ConectorAACR: string;
   pParent: TMARC21_Campo );
begin

  Self.cIDCampo         := id_campo;
  Self.cIDSubCampo      := id_subcampo;
  Self.cDescripcion_ESP := c_DescripESP;
  Self.cDescripcion_ENG := c_DescripENG;
  Self.cNota            := '';
  Self.cURL             := '';
  Self.Valor            := c_ValorDefault;

  Self.cTesauro         := c_Tesauro;
  Self.cConectorAACR    := c_ConectorAACR;

  Self.Parent           := pParent;

  Self.bObsoleto        := false;

  Self.cRepetible_SN    := '';

  if( Pos( '(NR)', Self.cDescripcion_ESP ) <> 0 ) then
  begin
     Self.cRepetible_SN := 'N';
     Delete( Self.cDescripcion_ESP, Pos( '(NR)', Self.cDescripcion_ESP ), 4 );
  end
  else if( Pos( '(R)', Self.cDescripcion_ESP ) <> 0 ) then
  begin
     Self.cRepetible_SN := 'S';
     Delete( Self.cDescripcion_ESP, Pos( '(R)', Self.cDescripcion_ESP ), 3 );
  end;

  Self.cTag := '';

end;

//
// Estas descripciones podrán ir en inglés o español
//
function TMARC21_SubCampo.ObtenerDescripcion: string;
begin

   Result := '';

   Result := cDescripcion_ESP;

   if Result = '' then
      Result := cDescripcion_ENG;

end;


//
// Por dificultades con los drivers de ODBC y datos
// estos datos se tienen que obtener por separado
//
function TMARC21_SubCampo.Obtener_URL_Nota_DesdeDB: boolean;
begin

   with Parent.Parent.Query do
   begin

       SQL.Clear;
       SQL.Add( 'SELECT NOTA, URL ' );
       SQL.Add( 'FROM marc_codigo21' );
       SQL.Add( 'WHERE ID_CAMPO="'+cIDCampo+'" and CODIGO="'+cIDSubCampo+'"' );
       Open;

       if not Eof then
       begin
           cUrl  := FieldByName('URL').AsString;
          cNota := FieldByName('NOTA').AsString;
          Result := true;
       end
       else
          Result := false;

       Close;

   end;

end;

// Devuelve un ID único para cada CAMPO + SUBCAMPO + OCURRENCIA
function TMARC21_SubCampo.ObtenerID: string;
var
   nPos: integer;
begin
   Result := Self.cIDSubCampo;

   nPos := Self.Parent.ObtenerIndexSubCampo( Self );

   if nPos <> -1 then
      Result := Result + ':' + IntToStr( nPos );

   Result := Result + '@'+Self.Parent.cIDCampo;
end;


//
// IDENTIFICADOR
//
constructor TMARC21_Identificador.Create( id_campo: string; id: integer; c_DescripESP, c_DescripENG, c_ValorDefault: string );
begin

   cIDCampo := id_campo;

   aValoresPosibles := TStringList.Create;

   cValor        := c_ValorDefault;
   //cValorDefault := c_ValorDefault;

   cDescripcion_ESP := c_DescripEsp;
   cDescripcion_ENG := c_DescripEng;

   nID := id;

end;

destructor TMARC21_Identificador.Destroy;
begin

   aValoresPosibles.Free;

end;

procedure TMARC21_Identificador.AgregarValorPosible( cSubCodigo: string;
  cDescripESP, cDescripENG: string );
var
  cValor: string;
begin

  // bilingue
  cValor := cDescripESP;

  if cValor = '' then
     cValor := cDescripENG;

  aValoresPosibles.Add( cSubCodigo+'- '+cValor );

end;

procedure TMARC21_Identificador.CargarValoresPosibles;
var
  cCodigo:string;
begin

   with Parent.Parent.Query do
   begin

       if nID = 1 then cCodigo := 'X1';
       if nID = 2 then cCodigo := 'X2';

       SQL.Clear;
       SQL.Add( 'SELECT SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, OBSOLETO ' );
       SQL.Add( 'FROM marc_codigo21' );
       SQL.Add( 'WHERE ID_CAMPO="'+cIDCampo+'" and CODIGO="'+cCodigo+'" and SUBCODIGO<>"" and NIVEL_MARC=6 and OBSOLETO <> "S" ' );
       SQL.Add( 'ORDER BY SUBCODIGO' );
       Open;

       while not Eof do
       begin

          AgregarValorPosible( FieldByName('SUBCODIGO').AsString,
                  FieldByName('DESCRIPCION').AsString,
                  FieldByName('DESCRIPCION_ORIGINAL').AsString );

          Next;
       end;

       Close;

   end;

end;

//
// Estas descripciones podrán ir en inglés o español
//
function TMARC21_Identificador.ObtenerDescripcion: string;
begin

   Result := '';

   Result := cDescripcion_ESP;

   if Result = '' then
      Result := cDescripcion_ENG;

end;

(*
constructor TMARC21_ElementoCabecera.Create;
begin

   aValoresPosibles := TStringList.Create;

   cDescripcion_ESP := '';
   cDescripcion_ENG := '';

   cValor := '';

end;

destructor TMARC21_ElementoCabecera.Destroy;
begin

   aValoresPosibles.Free;

end;


//
// Estas descripciones podrán ir en inglés o español
//
function TMARC21_ElementoCabecera.ObtenerDescripcion: string;
begin

   Result := '';

   Result := cDescripcion_ESP;

   if Result = '' then
      Result := cDescripcion_ENG;
end;

*)

//
// Constructor de Campo MARC
//
constructor TMARC21_Campo.Create( id_campo: string; pParent: TMARC21_Registro; c_DescripESP, c_DescripENG: string );
begin

  Parent := pParent;

  cIDCampo := id_campo;

  cDescripcion_ESP := c_DescripESP;
  cDescripcion_ENG := c_DescripENG;

  cUrl  := '';
  cNota := '';

  objID1 := nil;
  objID2 := nil;

  cID := '';

  ElementosEncabezado := TObjectList.Create;
  SubCampos := TObjectList.Create;

  bEncabezado := false;
  bDirectorio := false;
  //bGeneralesCampo008 := false;

  bAutomatico := false;

  ActualizaRepetible;

  bObsoleto   := false;

  if( id_campo = '$$$' ) then bEncabezado := true;
  if( id_campo = '###' ) then bDirectorio := true;
  //if( id_campo = '008' ) then bGeneralesCampo008 := true;

  bSinSubCampos := false;

  cValor :='';

end;

//
// Coloca un valor
//
function TMARC21_Campo.GetValor: string;
begin

  if bSinSubCampos then
     Result := cValor
  else
     Result := Self.ConcatenarValores(false,true);

end;

procedure TMARC21_Campo.SetValor( cNewValue: string );
begin

  if bSinSubCampos then
     cValor := cNewValue
  else
     raise TMARCError.Create('No se puede asignar valor al campo '+Self.cIDCampo );

end;

//
// elimina los (R) o (NR) y actualiza el campo cRepetible_SN
//
procedure TMARC21_Campo.ActualizaRepetible;
begin

  cRepetible_SN    := '';

  if( Pos( '(NR)', cDescripcion_ESP ) <> 0 ) then
  begin
     cRepetible_SN := 'N';
     Delete( cDescripcion_ESP, Pos( '(NR)', cDescripcion_ESP ), 4 );
  end
  else if( Pos( '(R)', cDescripcion_ESP ) <> 0 ) then
  begin
     cRepetible_SN := 'S';
     Delete( cDescripcion_ESP, Pos( '(R)', cDescripcion_ESP ), 3 );
  end;

end;

destructor TMARC21_Campo.Destroy;
//var
//  i: integer;
//  xObjMarc21: TMARC21_SubCampo;
begin

  if objID1 <> nil then objID1.Free;
  if objID2 <> nil then objID2.Free;

  //for i := 0 to SubCampos.Count - 1 do
  //begin
  //  xObjMarc21 := TMARC21_SubCampo(SubCampos.Items[i]);
  //  xObjMarc21.Free;
  //end;

  ElementosEncabezado.Free;
  SubCampos.Free;  // Este método libera objetos

end;


//
// Carga la definición del campo desde el catálogo
// Esta función se usa cuando se cargan creando campos en run-time
// a veces se no necesita la definición, pero otras veces se necesita
// 26mar2009
//
function TMARC21_Campo.CargarDefinicion: boolean;
begin

   Result := false;

   bSinSubCampos := false;

   with Parent.pQuery do
   begin

       // El sig. SELECT
       // también verifica si tiene subcampos
       SQL.Clear;
       SQL.Add( 'SELECT a.NIVEL_MARC, a.OBSOLETO, a.CODIGO, a.SUBCODIGO, a.DESCRIPCION, a.DESCRIPCION_ORIGINAL, a.NOTA, a.AUTOMATICO, a.TESAURO, a.CONECTOR_AACR, ' );
       SQL.Add( '  (SELECT COUNT(*) FROM marc_codigo21 WHERE ID_CAMPO="'+Self.cIDCampo+'" and NIVEL_MARC=9 ) AS NUMCAMPOS ' );
       SQL.Add( 'FROM marc_codigo21 a' );
       SQL.Add( 'WHERE a.ID_CAMPO="'+Self.cIDCampo+'" and a.NIVEL_MARC=1' );
       //SaveToFile( 'c:\test.sql' );
       Open;

       if not Eof then
       begin

           Self.cDescripcion_ESP := FieldByName('DESCRIPCION').AsString;
           Self.cDescripcion_ENG := FieldByName('DESCRIPCION_ORIGINAL').AsString;
           Self.cNota            := FieldByName('NOTA').AsString;
           Self.bObsoleto        := FieldByName('OBSOLETO').AsString = 'S';
           Self.bAutomatico      := FieldByName('AUTOMATICO').AsString = 'S';
           Self.ActualizaRepetible;

           bSinSubCampos := FieldByName('NUMCAMPOS').AsInteger = 0;

           Result := true;

       end;

       Close;

   end;

end;

//
// Estas descripciones podrán ir en inglés o español
//
function TMARC21_Campo.ObtenerDescripcion: string;
begin

   Result := '';

   Result := cDescripcion_ESP;

   if Result = '' then
      Result := cDescripcion_ENG;

end;


(*procedure TMARC21_Campo.AgregarElementoDeCabecera( cDato: String; cDescripESP, cDescripENG, cValorDefault: string );
var
  xObjMarc21_Encabezado: TMARC21_ElementoCabecera;

begin

  xObjMarc21_Encabezado := TMARC21_ElementoCabecera.Create;
  xObjMarc21_Encabezado.cIDCampo    := cIDCampo;
  xObjMarc21_Encabezado.cDato       := cDato;

  xObjMarc21_Encabezado.cDescripcion_ESP := cDescripESP;
  xObjMarc21_Encabezado.cDescripcion_ENG := cDescripENG;

  xObjMarc21_Encabezado.cPosiciones := cDato;

  xObjMarc21_Encabezado.cValor        := cValorDefault;

  ElementosEncabezado.Add( xObjMarc21_Encabezado );

end;
 *)
 
procedure TMARC21_Campo.AgregarIdentificador( nID: integer; cDescripESP, cDescripENG, cValorDefault: string );
begin

  if nID=1 then
  begin
     objID1 := TMARC21_Identificador.Create( cIDCampo, 1, cDescripESP, cDescripENG, cValorDefault );
     objID1.Parent := Self;
  end;

  if nID=2 then
  begin
     objID2 := TMARC21_Identificador.Create( cIDCampo, 2, cDescripESP, cDescripENG, cValorDefault );
     objID2.Parent := Self;
  end;

end;

procedure TMARC21_Campo.AgregarSubCampo( cID: string; cDescripESP, cDescripENG: string; cValorDefault, cTesauro, cConectorAACR: string; bForzarDuplicado: boolean=false  );
var
  xObjMarc21: TMARC21_SubCampo;

begin

  xObjMarc21 := TMARC21_SubCampo.Create( cIDCampo, cID, cDescripESP, cDescripENG, cValorDefault, cTesauro, cConectorAACR, Self );

  SubCampos.Add( xObjMarc21 );

  xObjMarc21.ID := xObjMarc21.ObtenerID;

end;


function TMARC21_Campo.ContarSubCampos: integer;
begin

  Result := SubCampos.Count;

end;

function TMARC21_Campo.SubCampo( idx: integer ): TMARC21_SubCampo;
begin

  if idx < SubCampos.Count then
  begin
     Result := TMARC21_SubCampo( SubCampos[ idx ] );
  end
  else
     Result := nil;

end;

function TMARC21_Campo.BuscarSubCampo( cDato: string ): TMARC21_SubCampo;
var
  i: integer;
  xObjMarc21: TMARC21_SubCampo;
begin

  Result := nil;

  for i := 0 to SubCampos.Count - 1 do
  begin
    xObjMarc21 := TMARC21_SubCampo(SubCampos.Items[i]);

    if xObjMarc21.cIDSubCampo = cDato then
    begin
       Result := xObjMarc21;
       break;
    end
  end;

end;

//
// Ofrece una búsqueda CERTERA DE cualquier ocurrencia de un subcampo
//
function TMARC21_Campo.BuscarSubCampo_X_ID( cID: string ): TMARC21_SubCampo;
var
  i: integer;
  xObjMarc21: TMARC21_SubCampo;

begin

  Result := nil;

  for i := 0 to SubCampos.Count - 1 do
  begin
    xObjMarc21 := TMARC21_SubCampo(SubCampos.Items[i]);

    if xObjMarc21.ID = cID then
    begin
       Result := xObjMarc21;
       break;
    end
  end;

end;


function TMARC21_Campo.ObtenerIndexSubCampo( objSubCampo: TMARC21_SubCampo ): integer;
var
  i: integer;

begin

  Result := -1;

  for i := 0 to SubCampos.Count - 1 do
  begin

    if TMARC21_SubCampo(SubCampos.Items[i]) = objSubCampo then
    begin
       Result := i;
       break;
    end;

  end;

end;

// 25mar2009
// cuenta ocurrencias de un subcampo
// veces que aparece un subcampo dentro del registro
function TMARC21_Campo.ContarOcurrencias( cSubCampo: string ): integer;
var
  i: integer;

begin

  Result := 0;

  for i := 0 to SubCampos.Count - 1 do
  begin

    if TMARC21_SubCampo(SubCampos.Items[i]).cIDSubCampo = cSubCampo then
       Result := Result + 1;

  end;

end;

function TMARC21_Campo.EliminarOcurrencia( objSubCampo: TMARC21_SubCampo ): boolean;
var
  i, ndx: integer;

begin

  Result := false;

  ndx := -1;

  for i := 0 to SubCampos.Count - 1 do
  begin

    if TMARC21_SubCampo(SubCampos.Items[i]) = objSubCampo then
    begin
       ndx := i;
       break;
    end;

  end;

  if ndx <> -1 then
  begin
     SubCampos.Delete(ndx);
     Result := true;
  end;

end;

// Devuelve un string con la concatenación de los subcampos 
function TMARC21_Campo.ConcatenarValores( concatenarConEspacios: boolean=true; incluirIDSubCampos: boolean = false ): string;
var
  i: integer;
  xObjSubCampo: TMARC21_SubCampo;

  nSubCampos: integer;

begin

  Result := '';

  if Self = nil then exit;

  nSubCampos := SubCampos.Count;

  for i := 0 to nSubCampos - 1 do
  begin

    xObjSubCampo := TMARC21_SubCampo( SubCampos.Items[i] );

    xObjSubCampo.cValor := Trim( xObjSubCampo.cValor );

    if xObjSubCampo.cValor <> '' then
    begin
       if Result <> '' then
       begin
          if xObjSubCampo.cConectorAACR <> '' then
          begin
             if Copy( Result, (Length(Result)-Length(xObjSubCampo.cConectorAACR))+1, Length(xObjSubCampo.cConectorAACR) ) <> xObjSubCampo.cConectorAACR then
             begin
                // no viene el conector : Agregarlo
                Result := Result + ' '+xObjSubCampo.cConectorAACR;

                if concatenarConEspacios then
                   Result := Result + ' ';
             end
             else
             begin
                // ya viene el conector registrado por usuario
                if Result[Length(Result)] <> ' ' then
                begin
                   if concatenarConEspacios then
                      Result := Result + ' ';  // agregar un espacio
                end;

             end;
          end
          else
          begin
             if concatenarConEspacios then
                Result := Result + ' ';
          end;
       end;

       // concatenar
       if (incluirIDSubCampos) and (nSubCampos>1) then
          Result := Result+xObjSubCampo.cIDSubCampo;

       Result := Result+xObjSubCampo.cValor;
    end;

    //if Result <> '' then
    //   if Pos( Result[Length(Result)], PUNTUACION_AACR ) = 0 then
    //      Result := Result + '.';

  end;

end;

(*function TMARC21_Campo.ContarElementosCabecera: integer;
begin

  Result := ElementosEncabezado.Count;

end;

function TMARC21_Campo.ElementoCabecera( idx: integer ): TMARC21_ElementoCabecera;
begin

  if idx < ElementosEncabezado.Count then
  begin
     Result := TMARC21_ElementoCabecera( ElementosEncabezado[ idx ] );
  end
  else
     Result := nil;

end;

function TMARC21_Campo.BuscarElementoCabecera( cDato: string ): TMARC21_ElementoCabecera;
var
   i: integer;
begin

   Result := nil;

   for i := 0 to ElementosEncabezado.Count - 1 do
   begin

      if TMARC21_ElementoCabecera(ElementosEncabezado.Items[i]).cDato = cDato then
      begin
         Result := TMARC21_ElementoCabecera(ElementosEncabezado.Items[i]);
         break;
      end;

   end;

end;

*)

//
// Por dificultades con los drivers de ODBC y datos
// estos datos se tienen que obtener por separado
//
function TMARC21_Campo.Obtener_URL_Nota_DesdeDB: boolean;
begin

   with Parent.Query do
   begin

       SQL.Clear;
       SQL.Add( 'SELECT NOTA, URL ' );
       SQL.Add( 'FROM marc_codigo21' );
       SQL.Add( 'WHERE ID_CAMPO="'+cIDCampo+'"' );
       Open;

       if not Eof then
       begin
          Self.cUrl  := FieldByName('URL').AsString;
          Self.cNota := FieldByName('NOTA').AsString;
          Result := true;
       end
       else
          Result := false;

       Close;

   end;

end;

// Devuelve un ID único para cada CAMPO + SUBCAMPO + OCURRENCIA
function TMARC21_Campo.ObtenerID: string;
var
   nPos: integer;
begin
   Result := 'F'+Self.cIDCampo;

   nPos := Self.Parent.ObtenerIndexCampo( Self );

   if nPos <> -1 then
      Result := Result + '_' + IntToStr( nPos );

end;

//
// permite agregar subcampos MARC
//
procedure TMARC21_Campo.AgregarSubCampos_DesdeCadenaMARC( cStr: string; cDelimitador: string = '$' );
var
   i: integer;
   nPos: integer;
   nPosFinSubCampo: integer;

   bNada: boolean;

   cExtracto: string;

   cIDSubCampo: string;
   cValor: string;

begin

   if Self.cIDCampo = '028' then
      nPos := 10;

   repeat

      if nPos = 10 then nPos := 0; // DEBUG

      nPos := 0;

      if cStr <> '' then
         if cStr[1] = cDelimitador[1] then
            nPos := 1;

      nPosFinSubCampo := -1;

      bNada := (nPos = 0);

      if bNada and (cStr <> '') then
      begin
         // no hay ningun indicador de campo
         // obtener el primer subcampo

         cStr := cDelimitador + 'a' + cStr;

         nPos := 1;
         //nPosFinSubCampo := Length(cStr);

         bNada := false;
      end;

      if not bNada then
      begin
         // ya se encontró un cDelimitador
         if nPosFinSubCampo = -1 then
         begin
            // buscar inicio de siguiente delimitador
            for i := 2 to Length(cStr) do
                if cStr[i] = cDelimitador[1] then
                begin
                   nPosFinSubCampo := i-1;
                   break;
                end;

            if nPosFinSubCampo = -1 then
              nPosFinSubCampo := Length(cStr);

         end;

         cExtracto := trim(Copy( cStr, nPos, nPosFinSubCampo ));
         cStr := trim(Copy( cStr, nPosFinSubCampo+1, 2048 ));

         if cExtracto <> '' then
         begin

            cIDSubCampo := Copy( cExtracto, 1, Self.Parent.FConteoCodigoSubcampo );
            cValor      := Copy( cExtracto, Self.Parent.FConteoCodigoSubcampo+1, 2048 );

            if (cIDSubCampo[1] = cDelimitador[1]) and (cDelimitador <> '$') then
            begin
               Delete( cIDSubCampo, 1, 1 );
               cIDSubCampo := '$'+cIDSubCampo;
            end;

            Self.AgregarSubCampo(cIDSubCampo,'','',cValor, '', '', true );

         end;

      end;



   until bNada;

end;


{* DEFINICION DE CLASE TMARC21_Registro *}
constructor TMARC21_Registro.Create;
begin

   nIDRed := -1;

   aCamposMarc := TStringList.Create;

   bConfigInit := false;

   Edit := false;

   {$IFDEF MODBC}
      pQuery := TMQuery.Create(nil);
      pQuery.DataBase := dmDatos.mDataBase1;
   {$ELSE}
      pQuery := TADOQuery.Create(nil);
      pQuery.Connection       := dmDatos.mDataBase1;
      pQuery.ConnectionString := dmDatos.mDataBase1.ConnectionString;
   {$ENDIF}

   nIDTitulo := 0;

   dFechaIngresoRegistro := 0;

   // Leader elements
   FLongitudRegistro     := 0;
   FEstadoRegistro       := '';
   FTipoRegistro         := '';
   FNivelBibliografico   := '';
   FTipoControl          := '';
   FCodigoEsquemaChars   := '';
   FConteoIndicadores    := 0;
   FConteoCodigoSubcampo := 0;
   FDireccionBase_Datos  := 0;
   FNivelCodificacion    := '';
   FFormaCatalogacion    := '';
   FNivelRegistro_Recurso := '';
   FMapaEntradas          := '';

   // elementos generales del campo 008
   F008_FechaIngresoRegistro := '';
   F008_TipoFechaEstadoPub   := '';
   F008_Fecha_1              := '';
   F008_Fecha_2              := '';
   F008_LugarPublicacion     := '';
   F008_Idioma               := '';
   F008_RegistroModificado   := '';
   F008_FuenteCatalogacion   := '';

end;


destructor TMARC21_Registro.Destroy;
var
  i: integer;
begin

  for i := 0 to aCamposMarc.Count - 1 do
      TMARC21_Campo(aCamposMarc.Objects[ i ]).Free;

  aCamposMarc.Free;

  pQuery.Free;

end;


procedure TMARC21_Registro.Clear;
var
  i: integer;
begin

  for i := 0 to aCamposMarc.Count - 1 do
      TMARC21_Campo(aCamposMarc.Objects[ i ]).Free;

  aCamposMarc.Clear;

end;

procedure TMARC21_Registro.InicializarRegistroXRango( cRango: string; bSoloDefinicionesDeCampo: boolean );
var
   nPosField: integer;
   objCampo: TMARC21_Campo;

begin

   with pQuery do
   begin

       SQL.Clear;
       SQL.Add( 'SELECT a.ID_CAMPO, a.CODIGO, a.DESCRIPCION, a.DESCRIPCION_ORIGINAL, a.OBSOLETO, a.TESAURO, a.CONECTOR_AACR ' );
       SQL.Add( 'FROM marc_codigo21 a ' );

       if cRango = '01X-04X' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "010" and a.ID_CAMPO < "050"' )
       else if cRango = '05X-08X' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "050" and a.ID_CAMPO < "090"' )
       else if cRango = '1XX' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "100" and a.ID_CAMPO < "200"' )
       else if cRango = '20X-24X' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "200" and a.ID_CAMPO < "250"' )
       else if cRango = '25X-27X' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "250" and a.ID_CAMPO < "280"' )
       else if cRango = '3XX' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "300" and a.ID_CAMPO < "400"' )
       else if cRango = '4XX' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "400" and a.ID_CAMPO < "500"' )
       else if cRango = '5XX' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "500" and a.ID_CAMPO < "600"' )
       else if cRango = '6XX' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "600" and a.ID_CAMPO < "700"' )
       else if cRango = '7XX' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "700" and a.ID_CAMPO < "800"' )
       else if cRango = '8XX' then
          SQL.Add( 'WHERE a.ID_CAMPO >= "800" and a.ID_CAMPO < "900"' )
       ;

       if bSoloDefinicionesDeCampo then
          SQL.Add( '   and a.NIVEL_MARC=1 ' );

       SQL.Add( 'ORDER BY a.ID_CAMPO' );
       //  SQL.SaveToFile( 'c:\test.sql' );
       Open;

       while not Eof do
       begin

          nPosField := aCamposMarc.IndexOf( FieldByName('ID_CAMPO').AsString );

          if nPosField = -1 then
          begin
             objCampo := TMARC21_Campo.Create( FieldByName('ID_CAMPO').AsString, Self,
                      FieldByName('DESCRIPCION').AsString, FieldByName('DESCRIPCION_ORIGINAL').AsString );
             //objCampo.cNota            := FieldByName('NOTA').AsString;
             objCampo.bObsoleto        := FieldByName('OBSOLETO').AsString='S';
             //objCampo.cUrl             := FieldByName('URL').AsString;

             aCamposMarc.AddObject( FieldByName('ID_CAMPO').AsString, objCampo );

             objCampo.ID := objCampo.ObtenerID;
          end
          else
             objCampo := TMARC21_Campo( aCamposMarc.Objects[ nPosField ] );

          if (FieldByName('ID_CAMPO').AsString = '$$$') then
          begin
             // agrega elementos de NIVEL_MARC 3
             // CABECERA

             if FieldByName('DATO').AsString = '05' then
                Self.Encabezado_EstadoRegistro := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '06' then
                Self.Encabezado_TipoRegistro := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '07' then
                Self.Encabezado_NivelBibliografico := FieldByName('VALOR_DEFAULT').AsString;
                
             if FieldByName('DATO').AsString = '08' then
                Self.Encabezado_TipoControl := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '17' then
                Self.Encabezado_NivelCodificacion := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '18' then
                Self.Encabezado_FormaCatalogacion := FieldByName('VALOR_DEFAULT').AsString;

//             objCampo.AgregarElementoDeCabecera(
  //                                         FieldByName('DATO').AsString,
    //                                       FieldByName('DEFAULT_DESCRIP').AsString,
      //                                     FieldByName('DEFAULT_DESCRIP_ORIGINAL').AsString,
        //                                   FieldByName('VALOR_DEFAULT').AsString );
          end
          else if FieldByName('ID_CAMPO').AsString = '008' then
          begin
          end
          else if FieldByName('CODIGO').AsString = 'X1' then
          begin
             objCampo.AgregarIdentificador( 1, FieldByName('ID_DESCRIP').AsString,
                                               FieldByName('ID_DESCRIP_ORIGINAL').AsString,
                                               FieldByName('VALOR_DEFAULT').AsString );
          end
          else if FieldByName('CODIGO').AsString = 'X2' then
          begin
             objCampo.AgregarIdentificador( 2, FieldByName('ID_DESCRIP').AsString,
                                               FieldByName('ID_DESCRIP_ORIGINAL').AsString,
                                               FieldByName('VALOR_DEFAULT').AsString );
          end
          else if FieldByName('CODIGO').AsString <> '' then
          begin
             if Copy( FieldByName('CODIGO').AsString, 1, 1 ) = '$' then
             begin
                objCampo.AgregarSubCampo( FieldByName('DATO').AsString,
                                          FieldByName('SUBC_DESCRIP').AsString,
                                          FieldByName('SUBC_DESCRIP_ORIGINAL').AsString,
                                          FieldByName('VALOR_DEFAULT').AsString,
                                          FieldByName('TESAURO').AsString,
                                          FieldByName('CONECTOR_AACR').AsString );
             end;
          end;

          Next;

       end;

       Close;

   end;

end;

procedure TMARC21_Registro.CargarConfig( nIDBiblioteca: integer );
begin

   if not bConfigInit then
   begin

       pQuery.SQL.Clear;
       pQuery.SQL.Add( 'SELECT a.*, b.ID_RED FROM cfgbiblioteca_config a LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) ' );
       pQuery.SQL.Add( 'WHERE a.ID_BIBLIOTECA='+IntToStr(nIDBiblioteca) );
       pQuery.Open;

       bAgregar_Puntuacion_Automatica := pQuery.FieldByName('CATALOG_PUNTUACION_AUTO').AsString = 'S';

       nIDRed := pQuery.FieldByName('ID_RED').AsInteger; 

       pQuery.Close;

       bConfigInit := true;

   end;

end;

procedure TMARC21_Registro.InicializarRegistroMARC21_DesdePlantilla( nBiblioteca, nPlantilla: integer );
var
   nPosField: integer;
   objCampo: TMARC21_Campo;

begin

   CargarConfig( nBiblioteca );

   with pQuery do
   begin

       SQL.Clear;
       SQL.Add( 'SELECT a.ID_CAMPO, a.DATO, a.VALOR_DEFAULT, b.DESCRIPCION, b.DESCRIPCION_ORIGINAL, b.OBSOLETO, ' );
       SQL.Add( '        x.DESCRIPCION AS DEFAULT_DESCRIP, x.DESCRIPCION_ORIGINAL AS DEFAULT_DESCRIP_ORIGINAL,' );
       SQL.Add( '         c.DESCRIPCION AS ID_DESCRIP, c.DESCRIPCION_ORIGINAL AS ID_DESCRIP_ORIGINAL, ' );
       SQL.Add( '          d.DESCRIPCION AS SUBC_DESCRIP, d.DESCRIPCION_ORIGINAL AS SUBC_DESCRIP_ORIGINAL, d.CONECTOR_AACR, d.TESAURO ' );
       SQL.Add( 'FROM cfgplantillas a' );
       SQL.Add( '  LEFT JOIN marc_codigo21 b ON (b.ID_CAMPO=a.ID_CAMPO and b.NIVEL_MARC=1)' );
       SQL.Add( '   LEFT JOIN marc_codigo21 x ON (x.ID_CAMPO=a.ID_CAMPO and x.CODIGO=a.DATO and x.SUBCODIGO=a.VALOR_DEFAULT and x.NIVEL_MARC=3)' );
       SQL.Add( '    LEFT JOIN marc_codigo21 c ON (c.ID_CAMPO=a.ID_CAMPO and c.CODIGO=a.DATO and c.NIVEL_MARC=5)' );
       SQL.Add( '      LEFT JOIN marc_codigo21 d ON (d.ID_CAMPO=a.ID_CAMPO and d.CODIGO=a.DATO and d.NIVEL_MARC=9)' );
       SQL.Add( 'WHERE (a.ID_BIBLIOTECA='+IntToStr(nBiblioteca)+' and a.ID_PLANTILLA='+IntToStr(nPlantilla)+') ' );
       SQL.Add( 'ORDER BY a.ID_CAMPO, a.DATO' );
       //L.SaveToFile( 'c:\test.sql' );
       Open;

       while not Eof do
       begin

          nPosField := aCamposMarc.IndexOf( FieldByName('ID_CAMPO').AsString );

          if nPosField = -1 then
          begin
             objCampo := TMARC21_Campo.Create( FieldByName('ID_CAMPO').AsString, Self,
                       FieldByName('DESCRIPCION').AsString, FieldByName('DESCRIPCION_ORIGINAL').AsString );
             objCampo.bObsoleto        := FieldByName('OBSOLETO').AsString='S';

             aCamposMarc.AddObject( FieldByName('ID_CAMPO').AsString, objCampo );

             objCampo.ID := objCampo.ObtenerID;
          end
          else
             objCampo := TMARC21_Campo( aCamposMarc.Objects[ nPosField ] );

          if (FieldByName('ID_CAMPO').AsString = '$$$') then
          begin
             // agrega elementos de NIVEL_MARC 3
             // CABECERA

             if FieldByName('DATO').AsString = '05' then
                Self.Encabezado_EstadoRegistro := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '06' then
                Self.Encabezado_TipoRegistro := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '07' then
                Self.Encabezado_NivelBibliografico := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '08' then
                Self.Encabezado_TipoControl := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '17' then
                Self.Encabezado_NivelCodificacion := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '18' then
                Self.Encabezado_FormaCatalogacion := FieldByName('VALOR_DEFAULT').AsString;

//             objCampo.AgregarElementoDeCabecera(
  //                                         FieldByName('DATO').AsString,
    //                                       FieldByName('DEFAULT_DESCRIP').AsString,
      //                                     FieldByName('DEFAULT_DESCRIP_ORIGINAL').AsString,
        //                                   FieldByName('VALOR_DEFAULT').AsString );
          end
          else if FieldByName('ID_CAMPO').AsString = '008' then
          begin

             if FieldByName('DATO').AsString = '00..05' then
                Self.F008_FechaIngresoRegistro := FieldByName('VALOR_DEFAULT').AsString
             else if FieldByName('DATO').AsString = '06' then
                Self.F008_TipoFechaEstadoPub   := FieldByName('VALOR_DEFAULT').AsString
             else if FieldByName('DATO').AsString = '15..17' then
                Self.F008_LugarPublicacion     := FieldByName('VALOR_DEFAULT').AsString
             else if FieldByName('DATO').AsString = '35..37' then
                Self.F008_Idioma               := FieldByName('VALOR_DEFAULT').AsString
             else if FieldByName('DATO').AsString = '38' then
                Self.F008_RegistroModificado   := FieldByName('VALOR_DEFAULT').AsString
             else if FieldByName('DATO').AsString = '39' then
                Self.F008_FuenteCatalogacion   := FieldByName('VALOR_DEFAULT').AsString;

          end
          else if FieldByName('DATO').AsString = 'X1' then
          begin
             objCampo.AgregarIdentificador( 1, FieldByName('ID_DESCRIP').AsString,
                                           FieldByName('ID_DESCRIP_ORIGINAL').AsString,
                                           FieldByName('VALOR_DEFAULT').AsString );
          end
          else if FieldByName('DATO').AsString = 'X2' then
          begin
             objCampo.AgregarIdentificador( 2, FieldByName('ID_DESCRIP').AsString,
                                           FieldByName('ID_DESCRIP_ORIGINAL').AsString,
                                           FieldByName('VALOR_DEFAULT').AsString );
          end
          else if FieldByName('DATO').AsString <> '' then
          begin
             if Copy( FieldByName('DATO').AsString, 1, 1 ) = '$' then
             begin
                objCampo.AgregarSubCampo( FieldByName('DATO').AsString,
                                           FieldByName('SUBC_DESCRIP').AsString,
                                            FieldByName('SUBC_DESCRIP_ORIGINAL').AsString,
                                             FieldByName('VALOR_DEFAULT').AsString,
                                              FieldByName('TESAURO').AsString,
                                               FieldByName('CONECTOR_AACR').AsString );
             end;
          end;

          Next;

       end;

       Close;

   end;

end;

//
// Inicializa un registro MARC desde la base de datos
// Exclusivamente para un título
//
// Inicialmente esta función se utilizará en Busquedas.pas
//
procedure TMARC21_Registro.InicializarRegistroMARC21_DesdeBD_Titulo( nBiblioteca: integer; nIDTitulo: double );
var
   nPosField: integer;
   objCampo: TMARC21_Campo;

begin

  CargarConfig( nBiblioteca );

  with pQuery do
  begin

       SQL.Clear;
       SQL.Add( 'SELECT a.ID_CAMPO, a.CODIGO, a.SUBCODIGO, a.ID1, a.ID2, a.VALOR, b.TESAURO, b.CONECTOR_AACR ' );
       SQL.Add( 'FROM acervo_catalogacion a' );
       SQL.Add( '   LEFT JOIN marc_codigo21 b ON (b.ID_CAMPO=a.ID_CAMPO and b.CODIGO=a.CODIGO and b.NIVEL_MARC=9)' );
       SQL.Add( 'WHERE (a.ID_BIBLIOTECA='+IntToStr(nBiblioteca)+' and a.ID_TITULO='+FloatToStr(nIDTitulo)+') ' );
       SQL.Add( 'ORDER BY a.ID_CAMPO, a.CODIGO' );
       SQL.SaveToFile( 'c:\test.sql' );
       Open;

       while not Eof do
       begin

          nPosField := aCamposMarc.IndexOf( FieldByName('ID_CAMPO').AsString );

          if nPosField = -1 then
          begin
             objCampo := TMARC21_Campo.Create( FieldByName('ID_CAMPO').AsString, Self, '', '' );
             objCampo.bObsoleto        := false; //FieldByName('OBSOLETO').AsString='S';

             aCamposMarc.AddObject( FieldByName('ID_CAMPO').AsString, objCampo );

             objCampo.ID := objCampo.ObtenerID;
          end
          else
             objCampo := TMARC21_Campo( aCamposMarc.Objects[ nPosField ] );

          if (FieldByName('ID_CAMPO').AsString = '$$$') then
          begin
             // agrega elementos de NIVEL_MARC 3
             // CABECERA

             if FieldByName('DATO').AsString = '05' then
                Self.Encabezado_EstadoRegistro := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '06' then
                Self.Encabezado_TipoRegistro := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '07' then
                Self.Encabezado_NivelBibliografico := FieldByName('VALOR_DEFAULT').AsString;
                
             if FieldByName('DATO').AsString = '08' then
                Self.Encabezado_TipoControl := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '17' then
                Self.Encabezado_NivelCodificacion := FieldByName('VALOR_DEFAULT').AsString;

             if FieldByName('DATO').AsString = '18' then
                Self.Encabezado_FormaCatalogacion := FieldByName('VALOR_DEFAULT').AsString;
//             objCampo.AgregarElementoDeCabecera(
  //                                         FieldByName('DATO').AsString,
    //                                       FieldByName('DEFAULT_DESCRIP').AsString,
      //                                     FieldByName('DEFAULT_DESCRIP_ORIGINAL').AsString,
        //                                   FieldByName('VALOR_DEFAULT').AsString );
          end
          else if FieldByName('ID_CAMPO').AsString = '008' then
          begin
          end
          else
          begin

              if FieldByName('ID1').AsString <> '' then
                 objCampo.AgregarIdentificador( 1, '',
                                                   '',
                                                   FieldByName('ID1').AsString );

              if FieldByName('ID2').AsString <> '' then
                 objCampo.AgregarIdentificador( 2, '',
                                                   '',
                                                   FieldByName('ID2').AsString );

          end;


          if FieldByName('CODIGO').AsString <> '' then
          begin
             if Copy( FieldByName('CODIGO').AsString, 1, 1 ) = '$' then
             begin
                objCampo.AgregarSubCampo( FieldByName('CODIGO').AsString,
                                           '',
                                            '',
                                             FieldByName('VALOR').AsString,
                                              FieldByName('TESAURO').AsString,
                                              FieldByName('CONECTOR_AACR').AsString );
             end;
          end;

          Next;

       end;

       Close;

  end;

end;

//
// Agrega un campo individual al objeto MARC21_Registro
// 24 mar 2009: Se agrega la característica para permitir duplicar
//
function TMARC21_Registro.AgregarCampo( cIDCodigo: string; bAgregarDefaults, bPermitirDuplicar: boolean ): TMARC21_Campo;
var
   nuevoCampo: TMARC21_Campo;

   bOk: boolean;

   cCodigo: string;

   cTemp: string;

   i, nUltimaPosicion: integer;

begin

   bOk := true;

   if not bPermitirDuplicar then
      bOk := BuscarCampoMARC( cIDCodigo ) = nil;

   Result := nil;

   if bOk then
   begin
      nuevoCampo := TMARC21_Campo.Create( cIDCodigo, Self, '', '' );

      cTemp := cIDCodigo;

      nUltimaPosicion := -1;

         for i := 0 to aCamposMarc.Count - 1 do
         begin
           if aCamposMarc.Strings[i] = cIDCodigo then
              nUltimaPosicion := i;
         end;

      if nUltimaPosicion <> -1 then
         aCamposMarc.InsertObject( nUltimaPosicion+1, cTemp, nuevoCampo )
      else
         aCamposMarc.AddObject( cTemp, nuevoCampo );

      nuevoCampo.ID := nuevoCampo.ObtenerID;

      if bAgregarDefaults then
      begin
         // hacer query a codigo MARC
         pQuery.SQL.Clear;
         pQuery.SQL.Add( 'SELECT a.NIVEL_MARC, a.OBSOLETO, a.CODIGO, a.SUBCODIGO, a.DESCRIPCION, a.DESCRIPCION_ORIGINAL, a.NOTA, a.TESAURO, a.AUTOMATICO, a.CONECTOR_AACR ' );
         pQuery.SQL.Add( 'FROM marc_codigo21 a' );
         pQuery.SQL.Add( 'WHERE (a.ID_CAMPO="'+cIDCodigo+'" and (a.NIVEL_MARC=1 or a.NIVEL_MARC=5 or a.NIVEL_MARC=6 or a.NIVEL_MARC=9))' );
         pQuery.SQL.Add( 'ORDER BY NIVEL_MARC, CODIGO' );
         //pQuery.SQL.SaveToFile( 'c:\test.sql' );
         pQuery.Open;

         nuevoCampo.bSinSubCampos := true;

         while not pQuery.Eof do
         begin

            if pQuery.FieldByName('NIVEL_MARC').asInteger = 1 then
            begin
               nuevoCampo.cDescripcion_ESP := pQuery.FieldByName('DESCRIPCION').AsString;
               nuevoCampo.cDescripcion_ENG := pQuery.FieldByName('DESCRIPCION_ORIGINAL').AsString;
               nuevoCampo.cNota            := pQuery.FieldByName('NOTA').AsString;
               nuevoCampo.bObsoleto        := pQuery.FieldByName('OBSOLETO').AsString = 'S';
               nuevoCampo.bAutomatico      := pQuery.FieldByName('AUTOMATICO').AsString = 'S';
               nuevoCampo.ActualizaRepetible;
            end
            else if pQuery.FieldByName('NIVEL_MARC').asInteger = 5 then
            begin

               if pQuery.FieldByName('OBSOLETO').AsString <> 'S' then
               begin
                  if pQuery.FieldByName('CODIGO').AsString = 'X1' then
                     nuevoCampo.AgregarIdentificador( 1,
                                                       pQuery.FieldByName('DESCRIPCION').AsString,
                                                       pQuery.FieldByName('DESCRIPCION_ORIGINAL').AsString, '' )
                  else if pQuery.FieldByName('CODIGO').AsString = 'X2' then
                     nuevoCampo.AgregarIdentificador( 2,
                                                       pQuery.FieldByName('DESCRIPCION').AsString,
                                                       pQuery.FieldByName('DESCRIPCION_ORIGINAL').AsString, '' );
               end;

            end
            else if pQuery.FieldByName('NIVEL_MARC').asInteger = 6 then
            begin
               // valores disponibles para indicadores
               if pQuery.FieldByName('CODIGO').AsString = 'X1' then
               begin

                  if (nuevoCampo.objID1 <> nil) and (pQuery.FieldByName('OBSOLETO').AsString <> 'S') then
                  begin
                    nuevoCampo.objID1.AgregarValorPosible( pQuery.FieldByName('SUBCODIGO').AsString,
                                                           pQuery.FieldByName('DESCRIPCION').AsString,
                                                           pQuery.FieldByName('DESCRIPCION_ORIGINAL').AsString );

                    if nuevoCampo.objID1.aValoresPosibles.Count = 1 then
                       nuevoCampo.objID1.cValor := pQuery.FieldByName('SUBCODIGO').AsString;
                  end;

               end
               else if pQuery.FieldByName('CODIGO').AsString = 'X2' then
               begin

                  if (nuevoCampo.objID2 <> nil) and (pQuery.FieldByName('OBSOLETO').AsString <> 'S') then
                  begin
                     nuevoCampo.objID2.AgregarValorPosible( pQuery.FieldByName('SUBCODIGO').AsString,
                                                            pQuery.FieldByName('DESCRIPCION').AsString,
                                                            pQuery.FieldByName('DESCRIPCION_ORIGINAL').AsString );

                     if nuevoCampo.objID2.aValoresPosibles.Count = 1 then
                        nuevoCampo.objID2.cValor := pQuery.FieldByName('SUBCODIGO').AsString;
                  end;

               end;

            end
            else if pQuery.FieldByName('NIVEL_MARC').asInteger = 9 then
            begin

               nuevoCampo.bSinSubCampos := false;

               if nuevoCampo.ContarSubCampos = 0 then
               begin
                  // Solamente agregará el primer SUBCAMPO alfabético
                  cCodigo := pQuery.FieldByName('CODIGO').AsString;

                  if cCodigo[1] = '$' then
                     cCodigo := Copy( cCodigo, 2, 10 );

                  if length(pQuery.FieldByName('CODIGO').AsString)>1 then
                     if not(cCodigo[1] in ['0'..'9']) then
                        nuevoCampo.AgregarSubCampo( pQuery.FieldByName('CODIGO').AsString,
                                                    pQuery.FieldByName('DESCRIPCION').AsString,
                                                    pQuery.FieldByName('DESCRIPCION_ORIGINAL').AsString, '',
                                                    pQuery.FieldByName('TESAURO').AsString,
                                                    pQuery.FieldByName('CONECTOR_AACR').AsString );
               end;

            end;

            pQuery.Next;
         end;

         pQuery.Close;

      end;

      Result := nuevoCampo;

   end;

end;

function TMARC21_Registro.ContarCampos: integer;
begin

   Result := aCamposMARC.Count;

end;

function TMARC21_Registro.ObtenerCampoMARC( idx: integer ) : TMARC21_Campo;
begin

  if idx < aCamposMARC.Count then
  begin
     Result := TMARC21_Campo( aCamposMARC.Objects[ idx ] );
  end
  else
     Result := nil;

end;

function TMARC21_Registro.BuscarCampoMARC( cCampoXBuscar: string ) : TMARC21_Campo;
var
  nPos: integer;
begin

  Result := nil;

  nPos := aCamposMarc.IndexOf( cCampoXBuscar );

  if nPos <> -1 then
     Result := TMARC21_Campo( aCamposMarc.Objects[ nPos ] );

end;


function TMARC21_Registro.BuscarCampoMARC_X_ID( cID: string ): TMARC21_Campo;
var
  i: integer;
  xObjCampo: TMARC21_Campo;

begin

  Result := nil;

  for i := 0 to aCamposMARC.Count - 1 do
  begin
    xObjCampo := TMARC21_Campo(aCamposMARC.Objects[i]);

    if xObjCampo.ID = cID then
    begin
       Result := xObjCampo;
       break;
    end
  end;

end;

//
// obtiene el index para cada objeto TMARC21_Campo
//
function TMARC21_Registro.ObtenerIndexCampo( objCampo: TMARC21_Campo ): integer;
var
  i: integer;

begin

  Result := -1;

  for i := 0 to aCamposMARC.Count - 1 do
  begin

    if TMARC21_Campo(aCamposMARC.Objects[i]) = objCampo then
    begin
       Result := i;
       break;
    end;

  end;


end;

function TMARC21_Registro.EliminarCampo( objCampo: TMARC21_Campo ): boolean;
var
  ndx, i: integer;
  xObjMarc21: TMARC21_Campo;

begin

  Result := false;

  ndx := -1;

  if objCampo <> nil then
  begin

      for i := 0 to ContarCampos do
      begin
        xObjMarc21 := Self.ObtenerCampoMARC(i);

        if xObjMarc21 = objCampo then
        begin

           ndx := i;

           Result := true;
           break;
        end
      end;

  end;

  if (Result) and (ndx<>-1) then
  begin
     xObjMarc21.Free;
     aCamposMarc.Delete(ndx);
  end;

end;


function TMARC21_Registro.EliminarSubCampo( objCampo: TMARC21_Campo; objSubCampo: TMARC21_SubCampo ): boolean;
var
  ndx, i: integer;
  xObjMarc21: TMARC21_SubCampo;

begin

  Result := false;

  ndx := -1;

  if objCampo <> nil then
  begin

      for i := 0 to objCampo.SubCampos.Count - 1 do
      begin
        xObjMarc21 := TMARC21_SubCampo(objCampo.SubCampos.Items[i]);

        if xObjMarc21 = objSubCampo then
        begin

           ndx := i;

           Result := true;
           break;
        end
      end;

  end;

  if (Result) and (ndx<>-1) then
     objCampo.SubCampos.Delete(ndx);

  if objCampo.ContarSubCampos = 0 then
     EliminarCampo( objCampo );

end;


// Modifica el valor de un campo dentro del registor MARC
function TMARC21_Registro.ModificarValorCampo( cIDCampo, cSubCodigo, cNuevoValor: string ): boolean;
var
  objCampo: TMARC21_Campo;

  //elementoCabecera: TMARC21_ElementoCabecera;

begin

  objCampo := BuscarCampoMARC( cIDCampo );

  Result := false;

  if objCampo <> nil then
  begin

     if objCampo.bEncabezado then
     begin
(*        elementoCabecera := objCampo.BuscarElementoCabecera( cSubCodigo );

        if elementoCabecera <> nil then
        begin
           elementoCabecera.cValor := cNuevoValor;
           Result := true;
        end;
  *)
     end
     else if objCampo.bDirectorio then
     begin
     end
     else
     begin
        // otros
     end;

     raise TMARCError.Create('PENDIENTE' );

  end;

end;

// Devuelve el valor de un campo dentro del registor MARC
function TMARC21_Registro.ObtenerValorCampo( cIDCampo, cCodigo, cSubCodigo: string ) : string;
var
  objCampo: TMARC21_Campo;
  objSubCampo: TMARC21_SubCampo;

  (* elementoCabecera: TMARC21_ElementoCabecera; *)

begin

  objCampo := BuscarCampoMARC( cIDCampo );

  Result := '';

  if objCampo <> nil then
  begin

     if objCampo.bEncabezado then
     begin
        Result := objCampo.Valor;

(*        elementoCabecera := objCampo.BuscarElementoCabecera( cCodigo );

        if elementoCabecera <> nil then
            Result := elementoCabecera.cValor;  *)

     end
     else if objCampo.bDirectorio then
     begin
     end
     else if objCampo.bSinSubCampos then
     begin
       Result := objCampo.Valor; 
     end
     else
     begin
        // otros
        objSubCampo := objCampo.BuscarSubCampo( cCodigo );

        if objSubCampo <> nil then
           Result := objSubCampo.cValor;

     end;

  end;

end;

(**********
 OPERACIONES EN BASE DE DATOS
 **)

function TMARC21_Registro.ObtenerDescripcionElementoTesauro( cCategoria, cIDValor: string ): string;
begin

  if Copy(cCategoria,1,1) = 'T' then
  begin
     // Fuente: TESAUROS
     pQuery.SQL.Clear;
     pQuery.SQL.Add( 'SELECT a.DESCRIPCION AS DESCRIP_CATEGORIA, a.CONTROL_ESTRICTO, c.TERMINO ' );
     pQuery.SQL.Add( 'FROM tesauro_categorias a' );
     pQuery.SQL.Add( '  LEFT JOIN tesauro_terminos_categorias b ON (b.ID_RED=a.ID_RED and b.ID_CATEGORIA=a.ID_CATEGORIA)' );
     pQuery.SQL.Add( '   LEFT JOIN tesauro_terminos c ON (c.ID_RED=b.ID_RED and c.ID_TERMINO=b.ID_TERMINO)' );
     pQuery.SQL.Add( 'WHERE (a.ID_RED='+IntToStr(nIDRed)+' and a.ID_CATEGORIA='+Copy(cCategoria,2,10)+')  and (c.CODIGO_CORTO="'+cIDValor+'")' );
  end;

  Result := '';

  pQuery.Open;

  if not pQuery.Eof then
     Result := pQuery.FieldByName('TERMINO').AsString;

end;

//PENDIENTE probar
//que los elementos de la ventana de captura
//obtengan el HINT desde aquí (agregar también a historial de cambios)
function TMARC21_Registro.ObtenerDescripcionSubcodigo( cCampo, cSubCampo, cCodigo: string ): string;
begin

  // Fuente: TESAUROS
  pQuery.SQL.Clear;
  pQuery.SQL.Add( 'SELECT a.DESCRIPCION AS DESCRIP ' );
  pQuery.SQL.Add( 'FROM marc_codigo21 a' );
  pQuery.SQL.Add( 'WHERE (a.ID_CAMPO="'+cCampo+'"  and a.CODIGO="'+cSubCampo+'" and a.SUBCODIGO="'+cCodigo+'")' );

  Result := '';

  pQuery.Open;

  if not pQuery.Eof then
     Result := pQuery.FieldByName('DESCRIP').AsString;

end;

// Agrega un registro a la tabla ACERVO_TITULOS
// Además agrega
function TMARC21_Registro.AgregarRegistro: boolean;
begin

   pQuery.SQL.Clear;
   pQuery.SQL.Add( 'SELECT COUNT(*) AS CUANTOS, MAX(ID_TITULO) AS MAXIMO FROM acervo_titulos' );
   pQuery.SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca) );
   pQuery.Open;

   if pQuery.FieldByName('CUANTOS').AsInteger = 0 then
      nIDTitulo := 1
   else
      nIDTitulo := pQuery.FieldByName('MAXIMO').AsInteger + 1;

   pQuery.Close;

   dFechaIngresoRegistro := Now;

   // Agregar el registro a ACERVO_TITULOS
   pQuery.SQL.Clear;
   pQuery.SQL.Add( 'INSERT INTO acervo_titulos' );
   pQuery.SQL.Add( ' (ID_BIBLIOTECA, ID_TITULO, ID_TIPOMATERIAL, STATUS, USUARIO_REGISTRO, FECHA_REGISTRO ) ' );
   pQuery.SQL.Add( ' VALUES ('+IntToStr(__IDBiblioteca)+', '+FloatToStr(nIDTitulo)+', "'+Self.Encabezado_TipoRegistro+'", "'+Self.Encabezado_EstadoRegistro+'", "'+__Usuario+'", :dFechaRegistro ) ' );

   {$IFDEF MODBC}
      pQuery.ParamByName('dFechaRegistro').AsDateTime := dFechaIngresoRegistro;
   {$ELSE}
      pQuery.Parameters.ParamByName('dFechaRegistro').Value := dFechaIngresoRegistro;
   {$ENDIF}

   pQuery.ExecSQL;

end;

// Actualiza o Agrega el Registro de Catalogación
// en tabla ACERVO_CATALOGACION
procedure TMARC21_Registro.AgregarActualizarCatalogacion_Valor( objCampo: TMARC21_Campo; cCodigo, cSubCodigo: string );
var
   bExiste: boolean;
   nIDDescriptor: integer;

   cID_Fields: string;
   cID_Values: string;

   cValor: string;

begin

   pQuery.SQL.Clear;
   pQuery.SQL.Add( 'SELECT COUNT(*) AS CUANTOS ' );
   pQuery.SQL.Add( 'FROM acervo_catalogacion' );
   pQuery.SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca)+' and ID_TITULO='+FloatToStr(nIDTitulo)+' and ID_CAMPO="'+objCampo.cIDCampo+'" and CODIGO="'+cCodigo+'" and SUBCODIGO="'+cSubCodigo+'"' );
   pQuery.SQL.Add( ' and INTERNALUSE1="'+objCampo.cID + ':{' + cCodigo + '}['+ cSubCodigo+']'+'"' );
   pQuery.Open;

   if pQuery.FieldByName('CUANTOS').AsInteger = 0 then
      bExiste := False
   else
      bExiste := True;

   pQuery.Close;

   pQuery.SQL.Clear;

   if not bExiste then
   begin
      // agregar registro DESCRIPTOR
      pQuery.SQL.Clear;
      pQuery.SQL.Add( 'SELECT COUNT(*) AS CUANTOS, MAX(ID_DESCRIPTOR) AS MAXIMO ' );
      pQuery.SQL.Add( 'FROM acervo_catalogacion' );
      pQuery.SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca)+' and ID_TITULO='+FloatToStr(nIDTitulo) );
      pQuery.Open;

      if pQuery.FieldByName('CUANTOS').AsInteger = 0 then
         nIDDescriptor := 1
      else
         nIDDescriptor := pQuery.FieldByName('MAXIMO').AsInteger + 1;

      pQuery.Close;

      cID_Fields := '';
      cID_Values := '';

      cValor := ObtenerValorCampo( objCampo.cIDCampo, cCodigo, cSubCodigo );

      //
      // G u a r d a r
      // i d e n t i f i c a d o r e s
      //
      if (objCampo.cIDCampo <> '$$$') and (objCampo.cIDCampo <> '###') then
      begin

          if (cCodigo = '') and (cSubCodigo='') then
          begin

              if objCampo.objID1 <> nil then
              begin
                 cID_Fields := 'ID1,';
                 cID_Values := '"'+objCampo.objID1.cValor+'",';
              end;

              if objCampo.objID2 <> nil then
              begin
                 cID_Fields := cID_Fields + 'ID2,';
                 cID_Values := cID_Values + '"'+objCampo.objID2.cValor+'",';
              end;

          end;
      end;

      pQuery.SQL.Clear;
      pQuery.SQL.Add( 'INSERT INTO acervo_catalogacion' );
      pQuery.SQL.Add( ' (ID_BIBLIOTECA, ID_TITULO, ID_DESCRIPTOR, ID_CAMPO, CODIGO, SUBCODIGO, '+cID_Fields+' VALOR, INTERNALUSE1 ) ' );
      pQuery.SQL.Add( 'VALUES ('+IntToStr(__IDBiblioteca)+', '+FloatToStr(nIDTitulo)+', '+IntToStr(nIDDescriptor)+', "'+objCampo.cIDCampo+'", "'+cCodigo+'", "'+cSubCodigo+'", '+cID_Values );
      pQuery.SQL.Add( ' :cValor, :cInternal ) ' );
      pQuery.Parameters.ParamByName('cValor').Value    := cValor;
      pQuery.Parameters.ParamByName('cInternal').Value := objCampo.cID + ':{' + cCodigo + '}['+ cSubCodigo+']';
      //pQuery.SQL.SaveToFile( 'c:\test.sql' );
      pQuery.ExecSQL;
   end
   else
   begin
      pQuery.SQL.Clear;
      pQuery.SQL.Add( 'UPDATE acervo_catalogacion' );
      pQuery.SQL.Add( ' SET VALOR=:cValor ' );
      pQuery.SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca)+' and ID_TITULO='+FloatToStr(nIDTitulo)+' and ID_DESCRIPTOR='+IntToStr(nIDDescriptor) );
      pQuery.Parameters.ParamByName('cValor').Value := cValor;
      pQuery.ExecSQL;
   end;

end;


// Inicia los campos de una transacción Edit/Insert
function TMARC21_Registro.IniciarGuardado: boolean;
begin

   //pQuery.SQL.Clear;
   //pQuery.SQL.Add( 'UPDATE acervo_catalogacion SET INTERNALUSE1="" ' );
   //pQuery.SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca)+' and ID_TITULO='+FloatToStr(nIDTitulo) );
   //pQuery.ExecSQL;

end;

// Concluye los campos de una transacción Edit/Insert
function TMARC21_Registro.FinalizarGuardado: boolean;
begin

   pQuery.SQL.Clear;
   pQuery.SQL.Add( 'UPDATE acervo_catalogacion SET INTERNALUSE1="" ' );
   pQuery.SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca)+' and ID_TITULO='+FloatToStr(nIDTitulo) );
   pQuery.ExecSQL;

end;

function TMARC21_Registro.AgregarDigitalizacion( var apImage1: TImage; var apImage2: TImage ): boolean;
//var
// Corriente1, Corriente2: TMemoryStream;

begin

  //
  // PORTADA
  //
(*  Corriente1 := nil;

  if apImage1.Picture.Bitmap <> nil then
  begin
     Corriente1 := TMemoryStream.Create;
     apImage1.Picture.Graphic.SaveToStream( Corriente1 );
     Corriente1.Seek( 0, soFromBeginning );
     Corriente1.Free;
  end;

  //
  // CONTRAPORTADA
  //
  Corriente2 := nil;

  if apImage1.Picture.Bitmap <> nil then
  begin
     Corriente2 := TMemoryStream.Create;
     apImage2.Picture.Graphic.SaveToStream( Corriente2 );
     Corriente2.Seek( 0, soFromBeginning );
     Corriente2.Free;
  end;
  *)

  if (apImage1.Picture.Width > 0) or (apImage2.Picture.Width > 0) then
  begin
    // Entramos con al menos una imagen
    pQuery.SQL.Clear;
    pQuery.SQL.Add( 'UPDATE acervo_titulos SET ' );

    if apImage1.Picture.Width > 0 then
       pQuery.SQL.Add( ' PORTADA=:pPortada,')
    else
       pQuery.SQL.Add( ' PORTADA=NULL,');

    if apImage2.Picture.Width > 0 then
       pQuery.SQL.Add( ' CONTRAPORTADA=:pContraPortada ')
    else
       pQuery.SQL.Add( ' CONTRAPORTADA=NULL ');

    pQuery.SQL.Add( 'WHERE ID_BIBLIOTECA='+IntToStr(__IDBiblioteca)+' and ID_TITULO='+FloatToStr(nIDTitulo) );

    if apImage1.Picture.Width > 0 then
       pQuery.Parameters.ParamByName('pPortada').Assign( apImage1.Picture.Graphic );

    if apImage2.Picture.Width > 0 then
       pQuery.Parameters.ParamByName('pContraPortada').Assign( apImage2.Picture.Graphic );

    pQuery.ExecSQL;
  end;

end;


//
// C A B E C E R A
//
// 26.mar.2009 = Se modifica para guardar en un solo valor el contenido del encabezado
//

procedure TMARC21_Registro.GuardarElementosEncabezado;
var
   objCampo: TMARC21_Campo;
begin

   objCampo := Self.BuscarCampoMARC('$$$');

   if objCampo <> nil then
      // Guardar campos CABECERA
      AgregarActualizarCatalogacion_Valor( objCampo, '', '' );    // Tipo de Material (Registro)

   //AgregarActualizarCatalogacion_Valor( objCampo, '05', '' );    // Estado del Registro
   //AgregarActualizarCatalogacion_Valor( objCampo, '07', '' );    // Nivel Bibliográfico
   //AgregarActualizarCatalogacion_Valor( objCampo, '17', '' );    // Nivel de Codificacion
   //AgregarActualizarCatalogacion_Valor( objCampo, '18', '' );    // Forma de Catalogacion

end;

procedure TMARC21_Registro.GuardarElementosSubCampos( objCampo: TMARC21_Campo );
var
   i: integer;

   (* objElementoCabecera: TMARC21_ElementoCabecera; *)
   objSubCampo: TMARC21_SubCampo;

begin

(*    //
    // G u a r d a r
    // e l e m e n t o s
    // d e   e n c a b e z a d o
    //

    for i := 0 to objCampo.ContarElementosCabecera do
    begin
        objElementoCabecera := objCampo.ElementoCabecera(i);

        if objElementoCabecera <> nil then
           AgregarActualizarCatalogacion_Valor( objCampo, objElementoCabecera.cDato, '' );

    end;

    *)

    //
    // G u a r d a r     i d e n t i f i c a d o r e s
    //
    if (objCampo.objID1 <> nil) or (objCampo.objID2 <> nil) then
    begin
       // XX = Codigo especial para Identificadores
       AgregarActualizarCatalogacion_Valor( objCampo, '', '' );
    end;


    if objCampo.bSinSubCampos then
       AgregarActualizarCatalogacion_Valor( objCampo, '', '' )
    else
    begin
        //
        // G u a r d a r     s u b c a m p o s
        //
        for i := 0 to objCampo.ContarSubCampos do
        begin
            objSubCampo := objCampo.SubCampo(i);

            if objSubCampo <> nil then
               AgregarActualizarCatalogacion_Valor( objCampo, objSubCampo.cIDSubCampo, '' );

        end;

    end;

end;


procedure TMARC21_Registro.ReasignarUniqueIDs;
var
  i: integer;
  xObjCampo: TMARC21_Campo;

begin

  for i := 0 to aCamposMARC.Count - 1 do
  begin
    xObjCampo := TMARC21_Campo(aCamposMARC.Objects[i]);
    xObjCampo.ID := xObjCampo.ObtenerID;

  end;

end;

function ObtenerVarChar( pField: TField ): string;
var
  tmpField: TBlobField;
begin

  tmpField := TBlobField( pField );

  Result := tmpField.AsString;

end;

// El encabezado debe venir así:
//  00-04: Longitud de registro
//  05:    Estado del registro
//  06:    Tipo del registro
//  07:    Nivel Bibliografico
//  08:    Tipo de control
//  09:    Código del Esquema de Caracteres
//  10:
//  11:
//  ... PENDIENTE COMPLEMENTAR
procedure TMARC21_Registro.InicializarCabecera( cValue: string );
const
  MARC_OFFSET = 1;
begin

  if( Length( cValue ) <> 24 ) then
     raise TMARCError.Create('Longitud incorrecta de encabezado')
  else
  begin

     // longitud
     Self.Encabezado_Longitud           := StrToInt(Copy( cValue, 0+MARC_OFFSET, 5 )); // 5 caracteres
     Self.Encabezado_EstadoRegistro     := Copy( cValue, 5+MARC_OFFSET, 1 ); // 1 caracter
     Self.Encabezado_TipoRegistro       := Copy( cValue, 6+MARC_OFFSET, 1 );    // 1 caracter
     Self.Encabezado_NivelBibliografico := Copy( cValue, 7+MARC_OFFSET, 1 ); // 1 caracter

     Self.Encabezado_TipoControl        := Copy( cValue, 8+MARC_OFFSET, 1 ); // 1 caracter
     Self.Encabezado_CodigoEsquemaChars := Copy( cValue, 9+MARC_OFFSET, 1 ); // 1 caracter
     Self.Encabezado_ConteoIndicadores  := StrToInt( Copy( cValue, 10+MARC_OFFSET, 1 )); // 1 caracteres

     Self.Encabezado_ConteoCodigoSubcampo  := StrToInt( Copy( cValue, 11+MARC_OFFSET, 1 )); // 1 caracteres
     Self.Encabezado_DireccionBase_Datos   := StrToInt( Copy( cValue, 12+MARC_OFFSET, 5 )); // 5 caracteres

     Self.Encabezado_NivelCodificacion     := Copy( cValue, 17+MARC_OFFSET, 1 ); // 1 caracteres
     Self.Encabezado_FormaCatalogacion     := Copy( cValue, 18+MARC_OFFSET, 1 ); // 1 caracteres
     Self.Encabezado_NivelRegistro_Recurso := Copy( cValue, 19+MARC_OFFSET, 1 ); // 1 caracteres
     Self.Encabezado_MapaEntradas          := Copy( cValue, 20+MARC_OFFSET, 4 ); // 4 caracteres

  end;

end;

//
// Generar una cabecera
//
procedure TMARC21_Registro.GenerarCabecera;
var
  objCampoCabecera: TMARC21_Campo;

  cValor: string;

  // coloca caracteres a la derecha de un string
  function padr( cStr: string; nLen: integer; cLlenar: string = ' '): string;
  var
     i: integer;
  begin

     Result := cStr;

     if Length(Result) < nLen then
        for i := 1 to nLen do
            Result := Result + cLlenar;

  end;

  function padl( cStr: string; nLen: integer; cLlenar: string = ' '): string;
  var
     i: integer;
  begin

     Result := cStr;

     if Length(Result) < nLen then
        for i := 1 to nLen do
            Result := cLlenar + Result;

  end;


begin
   //
   objCampoCabecera := Self.BuscarCampoMARC('$$$');

   cValor := '';

   // verificar que campo cabecera exista
   if objCampoCabecera <> nil then
   begin
      // Tipo Material (de Registro
      cValor := padl(IntToStr(Encabezado_Longitud),5,'0');

      cValor := cValor + padr(Encabezado_EstadoRegistro,1);
      cValor := cValor + padr(Encabezado_TipoRegistro,1);
      cValor := cValor + padr(Encabezado_NivelBibliografico,1);
      cValor := cValor + padr(Encabezado_TipoControl,1);

      cValor := cValor + padr(Encabezado_CodigoEsquemaChars,1);
      cValor := cValor + padr(IntToStr(Encabezado_ConteoIndicadores),1);
      cValor := cValor + padr(IntToStr(Encabezado_ConteoCodigoSubcampo),1);

      cValor := cValor + padl(IntToStr(Encabezado_DireccionBase_Datos),5,'0');

      cValor := cValor + padr(Encabezado_NivelCodificacion,1);
      cValor := cValor + padr(Encabezado_FormaCatalogacion,1);
      cValor := cValor + padr(Encabezado_NivelRegistro_Recurso,1);

      cValor := cValor + padr(Encabezado_MapaEntradas,4);

      if Length(cValor) <> 24 then
         raise TMARCError.Create('Se generó una cabecera de Longitud incorrecta' );

      objCampoCabecera.Valor := cValor;
   end;

end;

// Protected methods of TMARC21_Registro
procedure TMARC21_Registro.SetLongitudRegistro( newValue: integer );
begin
   Self.FLongitudRegistro := newValue;
end;

procedure TMARC21_Registro.SetEstadoRegistro( newValue: string );
begin
   Self.FEstadoRegistro := newValue;
end;

procedure TMARC21_Registro.SetTipoRegistro( newValue: string );
begin
  Self.FTipoRegistro := newValue;
end;

procedure TMARC21_Registro.SetNivelBibliografico( newValue: string );
begin
  Self.FNivelBibliografico := newValue;
end;

procedure TMARC21_Registro.SetTipoControl( newValue: string );
begin
  Self.FTipoControl := newValue;
end;

procedure TMARC21_Registro.SetCodigoEsquemaChars( newValue: string );
begin
  Self.FCodigoEsquemaChars := newValue;
end;

// Número de posiciones de caracteres que usan
// los indicadores en un registro MARC  p.e. 012 AB
// donde AB son los indicadores
//
// IMPORTANTE: Este valor siempre debería ser 2
//
procedure TMARC21_Registro.SetConteoIndicadores( newValue: integer );
begin
  if newValue <> 2 then
     raise TMARCError.Create('Valor de posiciones de indicadores diferente de 2')
  else
     Self.FConteoIndicadores := newValue;
end;

// Número de posiciones de caracteres utilizadas
// para cada código de subcampo en un registro MARC  p.e. $a, |a
//
// IMPORTANTE: Este valor siempre debería ser 2
//
procedure TMARC21_Registro.SetConteoCodigoSubcampo( newValue: integer );
begin
  if newValue <> 2 then
     raise TMARCError.Create('Valor de posiciones de subcampos diferente de 2')
  else
     Self.FConteoCodigoSubcampo := newValue;
end;

procedure TMARC21_Registro.SetDireccionBase_Datos( newValue: integer );
begin
  Self.FDireccionBase_Datos := newValue;
end;

procedure TMARC21_Registro.SetNivelCodificacion( newValue: string );
begin
  Self.FNivelCodificacion := newValue;
end;

procedure TMARC21_Registro.SetFormaCatalogacion( newValue: string );
begin
  Self.FFormaCatalogacion := newValue;
end;

procedure TMARC21_Registro.SetNivelRegistro_Recurso( newValue: string );
begin
  Self.FNivelRegistro_Recurso := newValue;
end;

procedure TMARC21_Registro.SetMapaEntradas( newValue: string );
begin
  if newValue <> '4500' then
     raise TMARCError.Create('Valor de mapa de entradas diferente de 4500')
  else
    Self.FMapaEntradas := newValue;
end;

end.
