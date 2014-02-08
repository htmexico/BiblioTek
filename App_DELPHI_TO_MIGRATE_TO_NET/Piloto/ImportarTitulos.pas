(*******
  Historial de Cambios:

  IMPORTAR UN REGISTRO MARC DE CATALOGACION

   26-mar-2009: Se inicia.

 **)

unit ImportarTitulos;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, StdCtrls, myform, clases_app_biblio, ComCtrls, RoundButton,
  ExtCtrls, xpGroupBox, xpPages;

type

  TfrmImportarTitulos = class(TFormSpecial)
    Label1: TLabel;
    Edit1: TEdit;
    CheckBox1: TCheckBox;
    Button1: TButton;
    Button2: TButton;
    Button3: TButton;
    xpPageControl1: TxpPageControl;
    xpTabSheet1: TxpTabSheet;
    xpTabSheet2: TxpTabSheet;
    xpTabSheet3: TxpTabSheet;
    Memo1: TMemo;
    xpGroupBox3: TxpGroupBox;
    Image1: TImage;
    btnCopyPortada: TEncarta;
    btmPastePortada: TEncarta;
    btnCopyPortadaFromFile: TEncarta;
    ListView1: TListView;
    xpGroupBox4: TxpGroupBox;
    Image2: TImage;
    btnCopyContra: TEncarta;
    btnPasteContra: TEncarta;
    btnCopyContraFromFile: TEncarta;
    procedure Button1Click(Sender: TObject);
    procedure Button2Click(Sender: TObject);
    procedure FormCreate(Sender: TObject);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);
    procedure Button3Click(Sender: TObject);
    procedure btmPastePortadaClick(Sender: TObject);
    procedure btnPasteContraClick(Sender: TObject);

  private
    { Private declarations }
    registroMarc21: TMARC21_Registro;

  public
    { Public declarations }
  end;


implementation

{$R *.dfm}

uses clipbrd, Unit1, Datos, Catalogacion;

procedure TfrmImportarTitulos.FormCreate(Sender: TObject);
begin
  Self.Top := 5;
  Self.Left := 5;

  Self.Width  := frmMenuPrincipal.Width - 260;
  Self.Height := frmMenuPrincipal.Height - 220;
end;

procedure TfrmImportarTitulos.FormClose(Sender: TObject;
  var Action: TCloseAction);
begin
  Action := caFree;
end;

procedure TfrmImportarTitulos.Button1Click(Sender: TObject);
var
  P: PChar;
  Buffer: array[0..1024] of Char;

begin

  P:= @Buffer;

  if Clipboard.HasFormat(CF_TEXT) then
    Clipboard.GetTextBuf(P,Sizeof(Buffer)-1);

  Memo1.Text:=P;

end;

procedure TfrmImportarTitulos.Button2Click(Sender: TObject);
const
  LEADER_LONGITUD = 1;
  LEADER_ESTADO   = 2;
  LEADER_TIPO     = 3;
  LEADER_NIVELBIB = 4;

var
  i: integer;

  cStr: string;

  objStrings: TStrings;

  cIDLinea: string;
  cCampo, cID: string;

  cLastCampo: string;

  objCampo, tmpCampo: TMARC21_Campo;

begin

  objStrings := TStringList.Create;

  if CheckBox1.Checked then
     objStrings.Assign( Memo1.Lines )
  else
  begin
     // leer la información plana
     // y colocarla en el array según la información del directorio
  end;

  registroMarc21 := TMARC21_Registro.Create;

  for i := 0 to objStrings.Count - 1 do
  begin

      cStr := objStrings.Strings[i];

      cIDLinea := Copy( cStr, 1, 7 );

      if Copy( cIDLinea, 1, 6 ) = 'LEADER' then
      begin
         //Self.DisplayStopMessage( 'LEADER', 'x' );
         cStr     := trim(Copy( cStr, 8, 2048 ));

         tmpCampo := registroMARC21.AgregarCampo( '$$$', false, true);
         tmpCampo.CargarDefinicion;
         tmpCampo.Valor := cStr;

         registroMARC21.InicializarCabecera( cStr );

      end
      else
      begin
         cCampo   := Copy( cIDLinea, 1, 7 ); // tomar 7 caracteres como ID del campo
                                         // incluyendo los indicadores
         cStr     := Copy( cStr, 8, 2048 );

         if cCampo <> '' then
         begin
            cLastCampo := Copy( cCampo, 1, 3 );
            cID        := Copy( cCampo, 5, registroMARC21.Encabezado_ConteoIndicadores );

            // se agrega la definición "plana" del campo
            // es decir: sin ningun indicador, ni tampo subcampos
            tmpCampo := registroMARC21.AgregarCampo( cLastCampo, false, true );
            tmpCampo.CargarDefinicion;

            if cID[1] <> ' ' then
               tmpCampo.AgregarIdentificador( 1, '', '', Copy(cID,1,1) );

            if cID[2] <> ' ' then
               tmpCampo.AgregarIdentificador( 2, '', '', Copy(cID,2,1) );

         end;

         if tmpCampo.bSinSubCampos then
            tmpCampo.Valor := cStr
         else
            tmpCampo.AgregarSubCampos_DesdeCadenaMARC( cStr, Edit1.Text  );

      end;

  end;

  objStrings.Free;

  ListView1.Items.Clear;

  for i := 0 to registroMARC21.ContarCampos-1 do
  begin
      tmpCampo := registroMARC21.ObtenerCampoMARC(i);

      cCampo := tmpCampo.cIDCampo;

      cCampo := cCampo + ' ';

      if tmpCampo.objID1 <> nil then
         cCampo := cCampo + tmpCampo.objID1.Valor
      else
         cCampo := cCampo + ' ';

      if tmpCampo.objID2 <> nil then
         cCampo := cCampo + tmpCampo.objID2.Valor
      else
         cCampo := cCampo + ' ';

      cCampo := cCampo + ' ';

      with ListView1.Items.Add do
      begin
         Caption := tmpCampo.ObtenerDescripcion;
         SubItems.Add( cCampo + tmpCampo.Valor );
      end;
  end;

  xpPageControl1.ActivePage := xpTabSheet3;

  if Self.DisplayQuestionMessage( 'Se ha leído el contenido del Registro MARC. ¿ Desea ingresarlo a la base de datos?', 'Aviso' ) then
  begin

       dmDatos.mDataBase1.BeginTrans;

       try
            registroMARC21.ReasignarUniqueIDs;

            if registroMARC21.Edit then
               registroMARC21.IniciarGuardado;

            if not registroMARC21.Edit then
               registroMARC21.AgregarRegistro;

            registroMARC21.AgregarDigitalizacion( Image1, Image2 );

            // Ir campo por campo
            for i := 0 to registroMARC21.ContarCampos - 1 do
            begin
               objCampo := registroMARC21.ObtenerCampoMARC(i);

               if (objCampo.bEncabezado) then
                  registroMARC21.GuardarElementosEncabezado
               else if objCampo.bDirectorio then
               begin
                  //

               end
               else
                  registroMARC21.GuardarElementosSubCampos( objCampo ); // otros campos

            end;

            //registroMARC21.FinalizarGuardado;

            dmDatos.mDataBase1.CommitTrans; // .Commit;

            Self.DisplayInfoMessage( 'Se ha registrado el material', 'Aviso' );

       except

            dmDatos.mDataBase1.RollbackTrans; // .Rollback;

            raise;

       end;

  end;

  registroMARC21.Free;

end;


procedure TfrmImportarTitulos.Button3Click(Sender: TObject);
begin
   Memo1.Text:= '';
end;

procedure TfrmImportarTitulos.btmPastePortadaClick(Sender: TObject);
begin
  if Clipboard.HasFormat(CF_BITMAP) then
  begin
    Image1.Picture.Bitmap.Assign( Clipboard );
    ConvertToJPEG(Image1);
  end;
end;

procedure TfrmImportarTitulos.btnPasteContraClick(Sender: TObject);
begin
  if Clipboard.HasFormat(CF_BITMAP) then
  begin
    Image2.Picture.Bitmap.Assign( Clipboard );
    ConvertToJPEG( Image2 );
  end;
end;

end.
