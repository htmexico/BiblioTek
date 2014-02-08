(*******
  Historial de Cambios:

  GENERAR EL REGISTRO DE EXISTENCIAS POR CADA TITULO 

   08-abr-2009: Se inicia.

 **)

unit Existencias;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, StdCtrls, myform, clases_app_biblio, ComCtrls, RoundButton,
  ExtCtrls, xpGroupBox, xpPages;

type

  TfrmExistencias = class(TFormSpecial)
    Label1: TLabel;
    Edit1: TEdit;
    CheckBox1: TCheckBox;
    Button1: TButton;
    Button2: TButton;
    Button3: TButton;
    procedure FormCreate(Sender: TObject);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);

  private
    { Private declarations }
    registroMarc21: TMARC21_Registro;

  public
    { Public declarations }
  end;


implementation

{$R *.dfm}

uses clipbrd, Unit1, Datos, Catalogacion;

procedure TfrmExistencias.FormCreate(Sender: TObject);
begin
  Self.Top := 5;
  Self.Left := 5;

  Self.Width  := frmMenuPrincipal.Width - 260;
  Self.Height := frmMenuPrincipal.Height - 220;
end;

procedure TfrmExistencias.FormClose(Sender: TObject;
  var Action: TCloseAction);
begin
  Action := caFree;
end;

end.
