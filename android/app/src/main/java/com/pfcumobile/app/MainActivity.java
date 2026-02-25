package com.pfcumobile.app;

import android.os.Bundle;
import android.webkit.WebView;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    @Override
    public void onResume() {
        super.onResume();
        // Force WebView to refocus and redraw to prevent "White Screen" freeze on
        // resume
        WebView webView = getBridge().getWebView();
        if (webView != null) {
            webView.requestFocus();
            webView.invalidate();
        }
    }
}
