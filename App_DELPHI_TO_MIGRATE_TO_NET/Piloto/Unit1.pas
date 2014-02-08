unit Unit1;

interface

uses
  Windows, Messages, SysUtils, Classes, Graphics, Controls, Forms, Dialogs,
  dxBar, ImgList, StdCtrls;

type

  TfrmMenuPrincipal = class(TForm)
    grupoges: TdxBarManager;
    frmSolicitudesOpcion: TdxBarButton;
    dxBarSubItem1: TdxBarSubItem;
    dxBarButton2: TdxBarButton;
    dxBarButton3: TdxBarButton;
    dxBarButton12: TdxBarButton;
    dxBarSubItem3: TdxBarSubItem;
    dxBarSubItem4: TdxBarSubItem;
    dxBarSubItem5: TdxBarSubItem;
    dxBarSubItem7: TdxBarSubItem;
    dxBarSubItem8: TdxBarSubItem;
    dxBarSubItem9: TdxBarSubItem;
    dxBarButton13: TdxBarButton;
    dxBarButton14: TdxBarButton;
    dxBarButton15: TdxBarButton;
    dxBarButton19: TdxBarButton;
    dxBarButton20: TdxBarButton;
    dxBarButton21: TdxBarButton;
    dxBarButton22: TdxBarButton;
    dxBarButton30: TdxBarButton;
    dxBarButton31: TdxBarButton;
    dxBarButton32: TdxBarButton;
    dxBarButton46: TdxBarButton;
    dxBarButton47: TdxBarButton;
    dxBarButton48: TdxBarButton;
    dxBarButton49: TdxBarButton;
    dxBarButton51: TdxBarButton;
    dxBarButton79: TdxBarButton;
    dxBarButton80: TdxBarButton;
    dxBarButton81: TdxBarButton;
    dxBarButton82: TdxBarButton;
    dxBarButton83: TdxBarButton;
    dxBarButton84: TdxBarButton;
    dxBarButton85: TdxBarButton;
    dxBarButton89: TdxBarButton;
    dxBarButton91: TdxBarButton;
    dxBarButton108: TdxBarButton;
    dxBarButton111: TdxBarButton;
    dxBarButton113: TdxBarButton;
    dxBarSubItem19: TdxBarSubItem;
    dxBarButton114: TdxBarButton;
    dxBarButton127: TdxBarButton;
    dxBarButton128: TdxBarButton;
    ImagesMenu: TImageList;
    ImageToolBar: TImageList;
    dxBarButton4: TdxBarButton;
    dxBarButton5: TdxBarButton;
    dxBarButton6: TdxBarButton;
    dxBarButton7: TdxBarButton;
    dxBarButton8: TdxBarButton;
    dxBarButton9: TdxBarButton;
    dxBarButton10: TdxBarButton;
    dxBarSubItem2: TdxBarSubItem;
    dxBarButton11: TdxBarButton;
    dxBarButton16: TdxBarButton;
    dxBarButton17: TdxBarButton;
    dxBarButton18: TdxBarButton;
    dxBarButton23: TdxBarButton;
    dxBarButton24: TdxBarButton;
    dxBarButton25: TdxBarButton;
    dxBarButton121: TdxBarButton;
    dxBarButton1: TdxBarButton;
    dxBarButton26: TdxBarButton;
    dxBarButton27: TdxBarButton;
    procedure dxBarButton12Click(Sender: TObject);
    procedure frmSolicitudesOpcionClick(Sender: TObject);
    procedure dxBarButton128Click(Sender: TObject);
    procedure dxBarButton13Click(Sender: TObject);
    procedure FormCreate(Sender: TObject);
    procedure dxBarButton121Click(Sender: TObject);
    procedure dxBarButton8Click(Sender: TObject);
    procedure dxBarButton1Click(Sender: TObject);
    procedure dxBarButton5Click(Sender: TObject);
    procedure dxBarButton27Click(Sender: TObject);
    procedure dxBarButton108Click(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  frmMenuPrincipal: TfrmMenuPrincipal;

  { variables globales }
  __IDBiblioteca: integer;
  __Usuario: string;

implementation

uses SolicitudesMaterial, FormBasica, Catalogacion, Busquedas, uXPIcons,
  ImportarTitulos, Existencias, AdminCuentas;

{$R *.DFM}

{$R icons.res}

procedure TfrmMenuPrincipal.FormCreate(Sender: TObject);
begin
   __IDBiblioteca := 1;

   __Usuario := 'admin';

end;


procedure TfrmMenuPrincipal.dxBarButton12Click(Sender: TObject);
begin
   Close;
end;

procedure TfrmMenuPrincipal.frmSolicitudesOpcionClick(Sender: TObject);
begin
   TfrmSolicitudes.Create(Application);
end;

procedure TfrmMenuPrincipal.dxBarButton128Click(Sender: TObject);
begin
   TfrmFormaBasica.Create(Application);
end;

procedure TfrmMenuPrincipal.dxBarButton13Click(Sender: TObject);
begin

  with TfrmCatalogacion.Create(Application) do
  begin
  end;

end;

procedure TfrmMenuPrincipal.dxBarButton121Click(Sender: TObject);
begin
  dxBarButton13Click(Sender);
end;


procedure TfrmMenuPrincipal.dxBarButton8Click(Sender: TObject);
begin

  with TfrmBusquedasTitulos.Create(Application) do
  begin
  end;

end;

procedure TfrmMenuPrincipal.dxBarButton1Click(Sender: TObject);
begin
  dxBarButton8Click(Sender);
end;

procedure TfrmMenuPrincipal.dxBarButton5Click(Sender: TObject);
begin

  with TfrmImportarTitulos.Create(Application) do
  begin
  end;

end;

procedure TfrmMenuPrincipal.dxBarButton27Click(Sender: TObject);
begin

  with TfrmExistencias.Create(Application) do
  begin
  end;

end;

procedure TfrmMenuPrincipal.dxBarButton108Click(Sender: TObject);
begin
  with TfrmAdminCuentas.Create(Application) do
  begin
  end;
end;

end.


