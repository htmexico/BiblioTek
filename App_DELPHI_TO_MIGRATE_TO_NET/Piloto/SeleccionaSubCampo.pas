(*******
  Historial de Cambios:

  AGREGAR SUBCAMPOS

   06-mar-2009: Se inicia.
 *)

unit SeleccionaSubCampo;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms, myform,
  clases_app_biblio, StdCtrls, RoundButton, CheckLst, Contnrs;

type

  TfrmSeleccionaSubCampo = class(TFormSpecial)
    chkLstCodificacionBasica: TCheckListBox;
    Encarta1: TEncarta;
    Encarta2: TEncarta;
    Label1: TLabel;
    procedure FormCreate(Sender: TObject);
    procedure FormDestroy(Sender: TObject);
    procedure Encarta1Click(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
    campoMARC: TMARC21_Campo;
    aSubCampos: TObjectList;
    aSubCampos_NUMEROS: TObjectList;

    primerSubCampoAgregado: TMARC21_SubCampo;

    procedure Init;
  end;

var
  frmSeleccionaSubCampo: TfrmSeleccionaSubCampo;

implementation

uses Datos;

{$R *.dfm}

procedure TfrmSeleccionaSubCampo.FormCreate(Sender: TObject);
begin
   campoMARC := nil;

   aSubCampos := TObjectList.Create;
   
   aSubCampos_NUMEROS             := TObjectList.Create;
   aSubCampos_NUMEROS.OwnsObjects := false;

   primerSubCampoAgregado := nil;

end;

procedure TfrmSeleccionaSubCampo.FormDestroy(Sender: TObject);
begin

   aSubCampos.Free;
   aSubCampos_NUMEROS.Free;

end;


procedure TfrmSeleccionaSubCampo.Init;
var
   i: integer;

   objTmp: TMARC21_SubCampo;

   cCaracter: string;
begin

   Label1.Caption := campoMARC.cIDCampo + ' ' + campoMARC.ObtenerDescripcion;

   aSubCampos.Clear;
   aSubCampos_NUMEROS.Clear;
   
   chkLstCodificacionBasica.Items.Clear;

   with dmDatos.qryAnySQL do
   begin
       SQL.Clear;
       SQL.Add( 'SELECT CODIGO, SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, OBSOLETO, TESAURO, CONECTOR_AACR ' );
       SQL.Add( 'FROM marc_codigo21 ' );
       SQL.Add( 'WHERE ID_CAMPO="'+campoMARC.cIDCampo+'" and CODIGO<>"" and NIVEL_MARC=9 ' );
       SQL.Add( 'ORDER BY CODIGO' );
       Open;

       while not Eof do
       begin
          objTmp := TMARC21_SubCampo.Create( campoMARC.cIDCampo,
                                             FieldByName('CODIGO').AsString,
                                             FieldByName('DESCRIPCION').AsString,
                                             FieldByName('DESCRIPCION_ORIGINAL').AsString, '',
                                             FieldByName('TESAURO').AsString,
                                             FieldByName('CONECTOR_AACR').AsString,
                                             campoMARC );

          objTmp.bObsoleto :=FieldByName('OBSOLETO').AsString = 'S';

          cCaracter := objTmp.cIDSubCampo;
          if length(cCaracter) > 1 then
             cCaracter := Copy( cCaracter, 2, 10 );

          if cCaracter[1] in ['0'..'9'] then
             aSubCampos_NUMEROS.Add( objTmp )
          else
             aSubCampos.Add( objTmp );

          Next;
       end;

       for i := 0 to aSubCampos_NUMEROS.Count-1 do
       begin
           aSubCampos.Add( aSubCampos_NUMEROS.Items[i] );
       end;

       // agregar los campos a la lista
       for i := 0 to aSubCampos.Count-1 do
       begin
          objTmp := TMARC21_SubCampo( aSubCampos.Items[i] );

          chkLstCodificacionBasica.Items.AddObject( objTmp.cIDSubCampo+' '+
                                                    objTmp.ObtenerDescripcion, objTmp );

          if objTmp.bObsoleto then
             chkLstCodificacionBasica.ItemEnabled[ chkLstCodificacionBasica.Items.Count-1 ] := false;

          if campoMARC.BuscarSubCampo( objTmp.cIDSubCampo ) <> nil then
             chkLstCodificacionBasica.ItemEnabled[ chkLstCodificacionBasica.Items.Count-1 ] := false;

       end;

       Close;
   end;

end;


procedure TfrmSeleccionaSubCampo.Encarta1Click(Sender: TObject);
var
  i: integer;

  objTmp: TMARC21_SubCampo;

begin

   if AcceptMessage then
   begin
      primerSubCampoAgregado := nil;

      for i := 0 to chkLstCodificacionBasica.Items.Count - 1 do
      begin

          if chkLstCodificacionBasica.Checked[i] then
          begin

             objTmp := TMARC21_SubCampo(aSubCampos.Items[i]);

             if primerSubCampoAgregado = nil then
                primerSubCampoAgregado := objTmp;

             campoMARC.AgregarSubCampo( objTmp.cIDSubCampo,
                                        objTmp.Descripcion_ESP,
                                        objTmp.Descripcion_ENG,
                                        '',
                                        objTmp.cTesauro,
                                        objTmp.cConectorAACR );

          end; { if }
      end;

      ModalResult := mrYes;

   end;

end;

end.
