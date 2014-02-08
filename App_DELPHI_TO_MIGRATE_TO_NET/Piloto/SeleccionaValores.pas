(*******
  Historial de Cambios:

  SELECCION DE VALORES PARA LA EDICION DE CODIGOS MARC

   12-feb-2009: Se inicia.
   25-mar-2009: Se agrega como referencia para obtener valores de
                NIVEL_MARC=10 en el catalogo MARC cuando
                estos pueden alimentar a un subcampo (NIVEL_MARC=9)

 **)
unit SeleccionaValores;

//{$DEFINE MODBC}

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, cxLookAndFeelPainters, StdCtrls, cxButtons, cxControls,
  cxContainer, cxListBox, DB, {$IFDEF MODBC}mQuery, exgrid, RapTree,
  FlytreePro{$ELSE}ADODB{$ENDIF}, RapTree, FlytreePro, exgrid;

type

  TfrmSeleccionaValores = class(TForm)
    cxButton1: TcxButton;
    cxButton2: TcxButton;
    Label1: TLabel;
    TreeView1: TFlyTreeViewPro;

    procedure cxButton2Click(Sender: TObject);
    procedure cxButton1Click(Sender: TObject);
    procedure FormCreate(Sender: TObject);
    procedure FormDestroy(Sender: TObject);

  private
    { Private declarations }
    nIDRed: integer;

    cCampo, cSubCampo: string;  // 25-mar-2009

    {$IFDEF MODBC}
      qryDataset: TmQuery;
    {$ELSE}
      qryDataset: TADOQuery;
    {$ENDIF}

  public
    { Public declarations }

    cValorSeleccionado: string;
    cDescripcionValorSeleccionado: string;

    procedure Reset( n_Red: integer );
    procedure InicializarInfo( cTitulo, cLeyenda: string );
    procedure InicializarValores( cStringFuente, cDefault: string; bJerarquizar: boolean );

    procedure TesauroDECodigoMARC( cIDCampo, cIDSubCampo: string );
  end;

var
  frmSeleccionaValores: TfrmSeleccionaValores;

implementation

uses Datos, Contnrs;

{$R *.dfm}

procedure TfrmSeleccionaValores.FormCreate(Sender: TObject);
begin

   {$IFDEF MODBC}
      qryDataset := TMQuery.Create(nil);
      qryDataset.DataBase := dmDatos.mDataBase1;
   {$ELSE}
      qryDataset := TADOQuery.Create(nil);
      qryDataset.Connection       := dmDatos.mDataBase1;
      qryDataset.ConnectionString := dmDatos.mDataBase1.ConnectionString;
   {$ENDIF}

end;

procedure TfrmSeleccionaValores.Reset( n_Red: integer );
begin
   Label1.Caption := '';

   cValorSeleccionado := '';
   cDescripcionValorSeleccionado := '';

   cCampo    := '';
   cSubCampo := '';

   treeView1.Items.Clear;

   Self.nIDRed := n_Red;

end;

procedure TfrmSeleccionaValores.InicializarInfo( cTitulo, cLeyenda: string );
begin
   Self.Caption := cTitulo;
   Label1.Caption := cLeyenda;
end;

procedure TfrmSeleccionaValores.TesauroDECodigoMARC( cIDCampo, cIDSubCampo: string );
begin

   Self.cCampo    := cIDCampo;
   Self.cSubCampo := cIDSubCampo;

end;

procedure TfrmSeleccionaValores.InicializarValores( cStringFuente, cDefault: string; bJerarquizar: boolean );
var
  nodePadre, nodeHijo, defaultNode: TFlyNode;

  objPadresList: TStringList;

  nPos: integer;

begin

  objPadresList := TStringList.Create;

  if bJerarquizar then
  begin
      qryDataset.SQL.Clear;

      if Copy(cStringFuente,1,1) = 'T' then
      begin
         // Fuente: TESAUROS
         qryDataset.SQL.Add( 'SELECT a.DESCRIPCION AS DESCRIP_CATEGORIA, a.CONTROL_ESTRICTO, c.* ' );
         qryDataset.SQL.Add( 'FROM tesauro_categorias a' );
         qryDataset.SQL.Add( '  LEFT JOIN tesauro_terminos_categorias b ON (b.ID_RED=a.ID_RED and b.ID_CATEGORIA=a.ID_CATEGORIA)' );
         qryDataset.SQL.Add( '   LEFT JOIN tesauro_terminos c ON (c.ID_RED=b.ID_RED and c.ID_TERMINO=b.ID_TERMINO)' );
         qryDataset.SQL.Add( 'WHERE (a.ID_RED='+IntToStr(nIDRed)+' and a.ID_CATEGORIA='+Copy(cStringFuente,2,10)+') and c.TERMINO_PADRE is NULL ' );
         qryDataset.SQL.Add( 'ORDER BY ID_TERMINO' );

         qryDataset.Open;

        // colocar objetos principales
        while not qryDataset.Eof do
        begin

           nodePadre := TreeView1.Items.Add( nil, qryDataset.FieldByName('TERMINO').AsString );

           objPadresList.AddObject( qryDataset.FieldByName('ID_TERMINO').AsString, nodePadre );

           qryDataset.Next;
        end;

        qryDataset.Close;

      end

  end
  else
  begin
     nodePadre := TreeView1.Items.Add( nil, '' );

     objPadresList.AddObject( '0', nodePadre );
  end;

  qryDataset.SQL.Clear;

  // COLOCAR TERMINOS HIJOS
  if Copy(cStringFuente,1,1) = 'T' then
  begin
     // Fuente: TESAUROS
     qryDataset.SQL.Add( 'SELECT a.DESCRIPCION AS DESCRIP_CATEGORIA, a.CONTROL_ESTRICTO, c.*' );
     qryDataset.SQL.Add( 'FROM tesauro_categorias a' );
     qryDataset.SQL.Add( '  LEFT JOIN tesauro_terminos_categorias b ON (b.ID_RED=a.ID_RED and b.ID_CATEGORIA=a.ID_CATEGORIA)' );
     qryDataset.SQL.Add( '   LEFT JOIN tesauro_terminos c ON (c.ID_RED=b.ID_RED and c.ID_TERMINO=b.ID_TERMINO)' );

     if not bJerarquizar then
        qryDataset.SQL.Add( 'WHERE (a.ID_RED='+IntToStr(nIDRed)+' and a.ID_CATEGORIA='+Copy(cStringFuente,2,10)+') ' )
     else
        qryDataset.SQL.Add( 'WHERE (a.ID_RED='+IntToStr(nIDRed)+' and a.ID_CATEGORIA='+Copy(cStringFuente,2,10)+') and c.TERMINO_PADRE is not NULL ' );

     qryDataset.SQL.Add( 'ORDER BY ID_TERMINO' );
  end
  else if cStringFuente = '{MARC}' then
  begin

     // Fuente: CODIGO MARC y NIVEL_MARC = 10
     qryDataset.SQL.Add( 'SELECT a.SUBCODIGO AS CODIGO_CORTO, ' );
     qryDataset.SQL.Add( '       a.DESCRIPCION AS TERMINO, "'+Label1.Caption+'" AS DESCRIP_CATEGORIA ' );
     qryDataset.SQL.Add( 'FROM marc_codigo21 a' );
     qryDataset.SQL.Add( 'WHERE a.ID_CAMPO="'+cCampo+'" and a.CODIGO="'+cSubCampo+'" and NIVEL_MARC=10 ' );
     qryDataset.SQL.Add( 'ORDER BY a.CODIGO, a.SUBCODIGO' );

  end;

  qryDataset.Open;

  defaultNode := nil;

  // colocar objetos principales
  while not qryDataset.Eof do
  begin

     nodePadre := nil;

     if not bJerarquizar then
        nPos := objPadresList.IndexOf( '0' )
     else
        nPos := objPadresList.IndexOf( qryDataset.FieldByName('TERMINO_PADRE').AsString );

     if nPos <> -1 then
     begin
        nodePadre := TFlyNode(objPadresList.objects[nPos]);

        if not bJerarquizar then
        begin
           if nodePadre.Caption = '' then
              nodePadre.Caption := qryDataset.FieldByName('DESCRIP_CATEGORIA').AsString;
        end;

     end;

     nodeHijo := TreeView1.Items.AddChild( nodePadre, qryDataset.FieldByName('TERMINO').AsString );
     nodeHijo.Cells[1] := qryDataset.FieldByName('CODIGO_CORTO').AsString;

     if qryDataset.FieldByName('CODIGO_CORTO').AsString = cDefault then
     begin
        defaultNode := nodeHijo;
     end;

     qryDataset.Next;
  end;

  qryDataset.Close;

  objPadresList.Free;

  if defaultNode <> nil then
  begin
     TreeView1.Selected := defaultNode;
     TreeView1.MakeVisible( TreeView1.Selected );
  end;

  if (cStringFuente = '{MARC}') and (defaultNode=nil) then
  begin
     if TreeView1.Items.Count > 0 then
        TreeView1.Items[0].Expand(true);
  end;

end;

procedure TfrmSeleccionaValores.cxButton2Click(Sender: TObject);
begin
   Close;
end;

procedure TfrmSeleccionaValores.cxButton1Click(Sender: TObject);
begin

   if TreeView1.Selected = nil then
   else
   begin
      cValorSeleccionado            := TreeView1.Selected.Cells[1];
      cDescripcionValorSeleccionado := TreeView1.Selected.Caption;
      ModalResult := mrYes;
   end;

end;


procedure TfrmSeleccionaValores.FormDestroy(Sender: TObject);
begin
   qryDataset.Free;
end;

end.
