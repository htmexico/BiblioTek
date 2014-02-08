(*******
  Historial de Cambios:

  FORMA DE CATALOGACION

   07-may-2009: Se inicia.

  PENDIENTES:

     - Al crear una red o biblioteca (definir cual???)
       el sistema deberá crear un kit completo de cosas predefinidas
       PLANTILLAS, GRUPOS, USUARIO, entre otros.

 *)
unit AdminCuentas;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, StdCtrls, DB, ADODB, Grids, DBGrids, ExDBGrid, RoundButton,
  ComCtrls, xpPages;

type

  TfrmAdminCuentas = class(TForm)
    Label1: TLabel;
    DataSource1: TDataSource;
    ADOQuery1: TADOQuery;
    btnCancel: TEncarta;
    btnSave: TEncarta;
    Encarta1: TEncarta;
    xpPageControl1: TxpPageControl;
    xpTabSheet1: TxpTabSheet;
    xpTabSheet2: TxpTabSheet;
    ExDBGrid1: TExDBGrid;
    ExDBGrid2: TExDBGrid;
    ADOQuery2: TADOQuery;
    DataSource2: TDataSource;
    procedure FormCreate(Sender: TObject);
    procedure btnCancelClick(Sender: TObject);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

implementation

uses Datos;

{$R *.dfm}

procedure TfrmAdminCuentas.FormCreate(Sender: TObject);
begin
   ADOQuery1.Open;
   ADOQuery2.Open;
end;

procedure TfrmAdminCuentas.btnCancelClick(Sender: TObject);
begin
    Close;
end;

procedure TfrmAdminCuentas.FormClose(Sender: TObject;
  var Action: TCloseAction);
begin
  Action := caFree;
end;

end.
