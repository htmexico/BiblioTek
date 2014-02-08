unit Unit2_MARC21;

interface

uses
  Windows, Messages, SysUtils, Classes, Graphics, Controls, Forms, Dialogs,
  StdCtrls, clipbrd;

type
  TForm2 = class(TForm)
    Memo1: TMemo;
    Button1: TButton;
    Label1: TLabel;
    Edit1: TEdit;
    CheckBox1: TCheckBox;
    Button2: TButton;
    procedure Button1Click(Sender: TObject);
    procedure Button2Click(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  Form2: TForm2;

implementation

{$R *.DFM}

procedure TForm2.Button1Click(Sender: TObject);
var
  P: PChar;
  Buffer: array[0..1024] of Char;
begin
  P:= @Buffer;
  if Clipboard.HasFormat(CF_TEXT) then
    Clipboard.GetTextBuf(P,Sizeof(Buffer)-1);
  Memo1.Text:=P;

end;

procedure TForm2.Button2Click(Sender: TObject);
var
  i: integer;

  cStr: string;

  objStrings: TStrings;

begin

  objStrings := TStringList.Create;

  if CheckBox1.Checked then
     objStrings.Assign( Memo1.Lines );

  for i := 1 to objStrings.Count - 1 do
  begin



  end;

  objStrings.Free;

end;

end.
