unit SeleccionaCampo;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, ComCtrls, exgrid, xpPages, StdCtrls, CheckLst,
  clases_app_biblio, RoundButton, myform;

type

  TfrmSeleccionaCampo = class(TFormSpecial)
    xpPageControl1: TxpPageControl;
    xpTabSheet1: TxpTabSheet;
    xpTabSheet2: TxpTabSheet;
    xpTabSheet3: TxpTabSheet;
    xpTabSheet4: TxpTabSheet;
    xpTabSheet5: TxpTabSheet;
    xpTabSheet6: TxpTabSheet;
    xpTabSheet7: TxpTabSheet;
    xpTabSheet8: TxpTabSheet;
    xpTabSheet9: TxpTabSheet;
    xpTabSheet10: TxpTabSheet;
    xpTabSheet11: TxpTabSheet;
    chkLstCodificacionBasica: TCheckListBox;
    chkLstClasificacion: TCheckListBox;
    chkLstAsientoPrincipal: TCheckListBox;
    chkLstTitulo: TCheckListBox;
    chkLstEdicionImpresion: TCheckListBox;
    chkLstDescripcionFisica: TCheckListBox;
    chkLstSeries: TCheckListBox;
    chkLstNotas: TCheckListBox;
    chkLstMaterias: TCheckListBox;
    chkLstAsientosSecundarios: TCheckListBox;
    chkLstAsientosSecs_Serie: TCheckListBox;
    Encarta1: TEncarta;
    Encarta2: TEncarta;
    procedure FormCreate(Sender: TObject);
    procedure FormDestroy(Sender: TObject);
    procedure xpPageControl1Change(Sender: TObject);
    procedure Encarta1Click(Sender: TObject);
  private
    { Private declarations }
    registroMarc21: TMARC21_Registro;

    procedure Reset;
    
  public
    { Public declarations }
    objetoMARC_EnUSO: TMARC21_Registro;

    aCampos: TStringList;

    procedure Init;
  end;

var
  frmSeleccionaCampo: TfrmSeleccionaCampo;

implementation

{$R *.dfm}

procedure TfrmSeleccionaCampo.FormCreate(Sender: TObject);
begin
   registroMarc21 := nil;

   aCampos := nil;
end;

procedure TfrmSeleccionaCampo.Reset;
begin
   //
end;

procedure TfrmSeleccionaCampo.Init;
begin
   //
   if registroMarc21 = nil then
   begin
      registroMarc21 := TMARC21_Registro.Create;  // Crear registro MARC21
      xpPageControl1Change( Self );
   end
   else
   begin
      Reset;
      xpPageControl1Change( Self );
   end;

   if aCampos <> nil then
      aCampos.Free;

   aCampos := TStringList.Create;

end;

procedure TfrmSeleccionaCampo.FormDestroy(Sender: TObject);
begin
   if registroMarc21 <> nil then
      registroMarc21.Free;  // Destruir registro MARC21
end;

procedure TfrmSeleccionaCampo.xpPageControl1Change(Sender: TObject);

   procedure ActualizarElementosEnLista( nListID:integer; chkAnyList: TCheckListBox );
   var
      i, nPos: integer;
      cStr: string;
   begin

      for i := 0 to chkAnyList.Items.Count - 1 do
      begin

          cStr := chkAnyList.Items[i];

          nPos := Pos( ' ', cStr );

          if nPos <> 0 then
             cStr := Copy( cStr, 1, nPos-1 );

          if objetoMARC_EnUSO.BuscarCampoMARC( cStr ) <> nil then
          begin
             chkAnyList.Checked[ i ] := true;
             chkAnyList.ItemEnabled[ i ] := false;
          end
          else
          begin
             chkAnyList.Checked[ i ] := false;
             chkAnyList.ItemEnabled[ i ] := true;
          end;
          
      end;

   end;

   procedure ColocarElementosEnLista( nListID:integer; chkAnyList: TCheckListBox );
   var
      i: integer;
      objCampo: TMARC21_Campo;

      bFlag: boolean;

   begin

      for i := 0 to registroMarc21.ContarCampos-1 do
      begin
          objCampo := registroMarc21.ObtenerCampoMARC( i );

          bFlag := false;

          if nListID = 1 then
             bFlag := (objCampo.cIDCampo >= '010') and (objCampo.cIDCampo < '050')
          else if nListID = 2 then
             bFlag := (objCampo.cIDCampo >= '050') and (objCampo.cIDCampo < '090')
          else if nListID = 3 then
             bFlag := (objCampo.cIDCampo >= '100') and (objCampo.cIDCampo < '200')
          else if nListID = 4 then
             bFlag := (objCampo.cIDCampo >= '200') and (objCampo.cIDCampo < '250')
          else if nListID = 5 then
             bFlag := (objCampo.cIDCampo >= '250') and (objCampo.cIDCampo < '280')
          else if nListID = 6 then
             bFlag := (objCampo.cIDCampo >= '300') and (objCampo.cIDCampo < '400')
          else if nListID = 7 then
             bFlag := (objCampo.cIDCampo >= '400') and (objCampo.cIDCampo < '500')
          else if nListID = 8 then
             bFlag := (objCampo.cIDCampo >= '500') and (objCampo.cIDCampo < '600')
          else if nListID = 9 then
             bFlag := (objCampo.cIDCampo >= '600') and (objCampo.cIDCampo < '700')
          else if nListID = 10 then
             bFlag := (objCampo.cIDCampo >= '700') and (objCampo.cIDCampo < '800')
          else if nListID = 11 then
             bFlag := (objCampo.cIDCampo >= '800') and (objCampo.cIDCampo < '900')

          ;

          if bFlag then
          begin
             chkAnyList.Items.Add( objCampo.cIDCampo+' '+objCampo.ObtenerDescripcion );

             if objCampo.bObsoleto then
                chkAnyList.ItemEnabled[ chkAnyList.Items.Count-1 ] := false;

             if objetoMARC_EnUSO.BuscarCampoMARC( objCampo.cIDCampo ) <> nil then
             begin
                chkAnyList.Checked[ chkAnyList.Items.Count-1 ] := true;
                chkAnyList.ItemEnabled[ chkAnyList.Items.Count-1 ] := false;
             end;

          end;
      end;

   end;

begin

   if xpPageControl1.ActivePage = xpTabSheet1 then
   begin

      if chkLstCodificacionBasica.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('01X-04X',true);
          ColocarElementosEnLista( 1, chkLstCodificacionBasica )
      end
      else
          ActualizarElementosEnLista( 1, chkLstCodificacionBasica );

   end
   else if xpPageControl1.ActivePage = xpTabSheet2 then
   begin

      if chkLstClasificacion.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('05X-08X',true);
          ColocarElementosEnLista( 2, chkLstClasificacion )
      end
      else
          ActualizarElementosEnLista( 2, chkLstClasificacion );

   end
   else if xpPageControl1.ActivePage = xpTabSheet3 then
   begin

      if chkLstAsientoPrincipal.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('1XX',true);
          ColocarElementosEnLista( 3, chkLstAsientoPrincipal )
      end
      else
          ActualizarElementosEnLista( 3, chkLstAsientoPrincipal );

   end
   else if xpPageControl1.ActivePage = xpTabSheet4 then
   begin

      if chkLstTitulo.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('20X-24X',true);
          ColocarElementosEnLista( 4, chkLstTitulo )
      end
      else
         ActualizarElementosEnLista( 4, chkLstTitulo );

   end
   else if xpPageControl1.ActivePage = xpTabSheet5 then
   begin

      if chkLstEdicionImpresion.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('25X-27X',true);
          ColocarElementosEnLista( 5, chkLstEdicionImpresion )
      end;

   end
   else if xpPageControl1.ActivePage = xpTabSheet6 then
   begin

      if chkLstDescripcionFisica.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('3XX',true);
          ColocarElementosEnLista( 6, chkLstDescripcionFisica )
      end;

   end
   else if xpPageControl1.ActivePage = xpTabSheet7 then
   begin

      if chkLstSeries.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('4XX',true);
          ColocarElementosEnLista( 7, chkLstSeries )
      end;

   end
   else if xpPageControl1.ActivePage = xpTabSheet8 then
   begin

      if chkLstNotas.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('5XX',true);
          ColocarElementosEnLista( 8, chkLstNotas )
      end;

   end
   else if xpPageControl1.ActivePage = xpTabSheet9 then
   begin

      if chkLstMaterias.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('6XX',true);
          ColocarElementosEnLista( 9, chkLstMaterias )
      end;

   end
   else if xpPageControl1.ActivePage = xpTabSheet10 then
   begin

      if chkLstAsientosSecundarios.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('7XX',true);
          ColocarElementosEnLista( 10, chkLstAsientosSecundarios )
      end;

   end
   else if xpPageControl1.ActivePage = xpTabSheet11 then
   begin

      if chkLstAsientosSecs_Serie.Items.Count = 0 then
      begin
          registroMARC21.InicializarRegistroXRango('8XX',true);
          ColocarElementosEnLista( 11, chkLstAsientosSecs_Serie )
      end;

   end;

end;

procedure TfrmSeleccionaCampo.Encarta1Click(Sender: TObject);

   procedure VerificarCheckList( chkAnyList: TCheckListBox );
   var
      i: integer;
      cStr: string;

   begin

      for i := 0 to chkAnyList.Items.Count - 1 do
      begin

          if (chkAnyList.Checked[i]) and (chkAnyList.ItemEnabled[i]) then
          begin

             cStr := chkAnyList.Items[i];

             if Pos( ' ', cStr ) <> 0 then
             begin

                cStr := Copy( cStr, 1, Pos( ' ', cStr )-1 );

                aCampos.Add( cStr );

             end;

             chkAnyList.ItemEnabled[i] := false;

          end;

      end;

   end;


begin

   if AcceptMessage then
   begin

      aCampos.Clear;

      VerificarCheckList(chkLstCodificacionBasica);
      VerificarCheckList(chkLstClasificacion);
      VerificarCheckList(chkLstAsientoPrincipal);
      VerificarCheckList(chkLstTitulo);
      VerificarCheckList(chkLstEdicionImpresion);
      VerificarCheckList(chkLstDescripcionFisica);
      VerificarCheckList(chkLstSeries);
      VerificarCheckList(chkLstNotas);
      VerificarCheckList(chkLstMaterias);
      VerificarCheckList(chkLstAsientosSecundarios);
      VerificarCheckList(chkLstAsientosSecs_Serie);

      ModalResult := mrYes;

   end;

end;

end.
