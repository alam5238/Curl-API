# Curl-API
API using raw Curl command for free PHP, MySql hosting server. All Request like, GET, POST, PUT, DELETE example are Implement here. Its very easy to use. No need any plugin or library.

## Step 1: First adding a permission on `AndroidMainfest.xml` file for Internet.
```
    <uses-permission android:name="android.permission.INTERNET"/>
    <uses-permission android:name="android.permission.ACCESS_NETWORK_STATE"/>
    <uses-permission android:name="android.permission.ACCESS_WIFI_STATE"/>
    <uses-permission android:name="android.permission.CHANGE_NETWORK_STATE"/>

```
Now, Add `android:usesCleartextTraffic="true"` inside `<application>` tag.
```
<application
        android:allowBackup="true"
        android:usesCleartextTraffic="true"
        tools:targetApi="31">
```
## Step 2: Now, Create a java file `HttpHelper` or `HttpHelper.java` for handraling API network request operation. Like method `GET/POST, API URL, and POST/GET data params`. Its return a JSON string for Response operation.
```
import javax.net.ssl.X509TrustManager;

public class HttpHelper {

    // Method to send a GET or POST request and return a JSON response string
    public static String getRequest(String urlString, String postDataString) {
        HttpURLConnection connection = null;
        try {

//Check Url https or http. if https then below code needed otherwise its not needed in http.if you can delete it for http.
            if(isHttps(urlString)){

                // Set up SSL to bypass certificate validation (equivalent to cURL --insecure)
                TrustManager[] trustAllCerts = new TrustManager[]{
                        new X509TrustManager() {
                            public java.security.cert.X509Certificate[] getAcceptedIssuers() {
                                return null;
                            }

                            public void checkClientTrusted(java.security.cert.X509Certificate[] certs, String authType) {
                            }

                            public void checkServerTrusted(java.security.cert.X509Certificate[] certs, String authType) {
                            }
                        }
                };

                // Install the all-trusting trust manager
                SSLContext sslContext = SSLContext.getInstance("TLS");
                sslContext.init(null, trustAllCerts, new java.security.SecureRandom());
                HttpsURLConnection.setDefaultSSLSocketFactory(sslContext.getSocketFactory());

                // Create a hostname verifier that doesn't verify the hostname
                HostnameVerifier allHostsValid = (hostname, session) -> true;
                HttpsURLConnection.setDefaultHostnameVerifier(allHostsValid);
            }

            // Create a URL object
            URL url = new URL(urlString);
            // Open the connection
            connection = (HttpURLConnection) url.openConnection();


            // Set headers (Use chrome Inspect option then network then right click request file and copy Curl for bash)
            connection.setRequestProperty("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7");
            connection.setRequestProperty("Accept-Language", "en-US,en;q=0.9,bn;q=0.8");
            connection.setRequestProperty("Cache-Control", "max-age=0");
            connection.setRequestProperty("Connection", "keep-alive");
            connection.setRequestProperty("Cookie", "__test=20f6fc07209afd885bd518ce6e3b108d");
            connection.setRequestProperty("Upgrade-Insecure-Requests", "1");
            connection.setRequestProperty("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36");


            // If postDataString is null, it's a GET request; otherwise, it's a POST request
            if (postDataString == null || postDataString.isEmpty()) {
                connection.setRequestMethod("GET");
            } else {
                connection.setRequestMethod("POST");
                connection.setDoOutput(true);  // Indicates POST request

                // Send POST data
                DataOutputStream outputStream = new DataOutputStream(connection.getOutputStream());
                outputStream.writeBytes(postDataString);
                outputStream.flush();
                outputStream.close();
                Log.d("HTTPHELPER", "Method POST");
            }




            // Get response code
            int responseCode = connection.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) { // Success
                BufferedReader in = new BufferedReader(new InputStreamReader(connection.getInputStream()));
                String inputLine;
                StringBuilder response = new StringBuilder();

                // Read the response
                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }
                in.close();

                // Return the response as a string
                return response.toString();
            } else {
                return "Error: " + responseCode;
            }
        } catch (Exception e) {
            e.printStackTrace();
            return "Exception: " + e.getMessage();
        } finally {
            if (connection != null) {
                connection.disconnect();
            }
        }
    }

//Both Method use for URL check if SSL or NOT
       // Method to check if the URL uses HTTPS
        public static boolean isHttps(String url) {
            return url.toLowerCase().startsWith("https://");
        }

        // Method to check if the URL uses HTTP
        public static boolean isHttp(String url) {
            return url.toLowerCase().startsWith("http://");
        }

}

```

## Step 3: Now, Call the HttpHelper for Any Activity. For this I use Mainactivity. first create `ExecutorService` and `Handler` constructor.
```

public class MainActivity extends AppCompatActivity {

    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    private final Handler handler = new Handler(Looper.getMainLooper());

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);
}
}

```
Then create API url and params data variable and call the `executor.execute()` for a request in a background thread to avoid blocking the UI and call HttpHelper with proper params and store data in String jsonResponse variable. `handler.post()` method represent the final response on UI thread.
```
public class MainActivity extends AppCompatActivity {

    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    private final Handler handler = new Handler(Looper.getMainLooper());

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);

  // Example usage of the helper method
        String url = "https://nazmulalamshuvo.42web.io/curl/api.php";
        String postData = "name=John&age=25";  // Post data can be null for GET requests

        // Make a request in a background thread to avoid blocking the UI
        executor.execute(() -> {
            // Use the helper class method to get a response from the server
            String jsonResponse = HttpHelper.getRequest(url, postData); // Can pass null for GET request

            // Post the result back to the main thread to update the UI
            handler.post(() -> { 
                Log.d("Response", jsonResponse);
            });
        });
}
}

```

## Step 4: Now, Configure the PHP server for reciving the incoming curl request. create a PHP file named `api.php` for deal all the request method.
```

<?php

// Set the response content type to JSON
header("Content-Type: application/json; charset=UTF-8");

// Get the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Function to get input data from the request body
function getInputData() {
    return json_decode(file_get_contents("php://input"), true);
}

// Handle different HTTP request methods (CRUD operations)
switch ($request_method) {
    case 'GET':
        // Handle GET request (e.g., retrieving data)
        handleGet();
        break;

    case 'POST':
        // Handle POST request (e.g., inserting data)
        handlePost();
        break;

    case 'PUT':
        // Handle PUT request (e.g., updating data)
        handlePut();
        break;

    case 'DELETE':
        // Handle DELETE request (e.g., deleting data)
        handleDelete();
        break;

    default:
        // Handle unsupported request methods
        echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
        break;
}

// Function to handle GET requests
function handleGet() {
    if (isset($_GET['name']) && isset($_GET['age'])) {
        $name = $_GET['name'];
        $age = $_GET['age'];

        // Simulate fetching data from a database
        echo json_encode([
            "status" => "success",
            "message" => "GET request received",
            "data" => [
                "name" => $name,
                "age" => $age
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid GET parameters"]);
    }
}

// Function to handle POST requests
function handlePost() {
    $input_data = getInputData();
    if(isset($_POST['name'])){
        echo json_encode([
            "status" => "success",
            "message" => "POST request received",
            "data" => [
                "name" => $_POST['name'],
                "age" => $_POST['age']
            ]
        ]);
    }else{
        echo json_encode(["status" => "error", "message" => "Invalid POST data"]);
    }
//if it not work then use upper code

    // if (isset($input_data['name']) && isset($input_data['age'])) {
    //     $name = $input_data['name'];
    //     $age = $input_data['age'];

    //     // Simulate inserting data into a database
    //     echo json_encode([
    //         "status" => "success",
    //         "message" => "POST request received",
    //         "data" => [
    //             "name" => $name,
    //             "age" => $age
    //         ]
    //     ]);
    // } else {
    //     echo json_encode(["status" => "error", "message" => "Invalid POST data"]);
    // }
}

// Function to handle PUT requests
function handlePut() {
    $input_data = getInputData();
    if (isset($input_data['id']) && isset($input_data['name']) && isset($input_data['age'])) {
        $id = $input_data['id'];
        $name = $input_data['name'];
        $age = $input_data['age'];

        // Simulate updating data in a database
        echo json_encode([
            "status" => "success",
            "message" => "PUT request received",
            "data" => [
                "id" => $id,
                "name" => $name,
                "age" => $age
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid PUT data"]);
    }
}

// Function to handle DELETE requests
function handleDelete() {
    $input_data = getInputData();
    if (isset($input_data['id'])) {
        $id = $input_data['id'];

        // Simulate deleting data from a database
        echo json_encode([
            "status" => "success",
            "message" => "DELETE request received",
            "data" => [
                "id" => $id
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid DELETE data"]);
    }
}

?>


```

## If You use on each activity or single activity then use this code. Its for `Mainactivity` as like all you make.
```
public class MainActivity extends AppCompatActivity {

    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    private final Handler handler = new Handler(Looper.getMainLooper());
    private TextView responseTextView; // TextView to display the response

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        responseTextView = findViewById(R.id.response_text_view); // Assuming you have a TextView in your layout

        // Send the HTTP POST request
         sendPOSTRequest("https://nazmulalamshuvo.42web.io/curl/api.php");

    }

//Use this method for fetching data by Curl on POST method

    private void sendPOSTRequest(String urlString) {
        executor.execute(() -> {
            try {
            
                // Set up SSL to bypass certificate validation (equivalent to cURL --insecure)
                TrustManager[] trustAllCerts = new TrustManager[]{
                        new X509TrustManager() {
                            public java.security.cert.X509Certificate[] getAcceptedIssuers() {
                                return null;
                            }

                            public void checkClientTrusted(java.security.cert.X509Certificate[] certs, String authType) {
                            }

                            public void checkServerTrusted(java.security.cert.X509Certificate[] certs, String authType) {
                            }
                        }
                };

                // Install the all-trusting trust manager
                SSLContext sslContext = SSLContext.getInstance("TLS");
                sslContext.init(null, trustAllCerts, new java.security.SecureRandom());
                HttpsURLConnection.setDefaultSSLSocketFactory(sslContext.getSocketFactory());

                // Create a hostname verifier that doesn't verify the hostname
                HostnameVerifier allHostsValid = (hostname, session) -> true;
                HttpsURLConnection.setDefaultHostnameVerifier(allHostsValid);


                // Open connection
                URL url = new URL(urlString);
                HttpsURLConnection connection = (HttpsURLConnection) url.openConnection();
                //connection.setRequestMethod("GET");

                //if GET request then delete those two line
                connection.setRequestMethod("POST");
                connection.setDoOutput(true);



                // Set headers
                connection.setRequestProperty("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7");
                connection.setRequestProperty("Accept-Language", "en-US,en;q=0.9,bn;q=0.8");
                connection.setRequestProperty("Cache-Control", "max-age=0");
                connection.setRequestProperty("Connection", "keep-alive");
                connection.setRequestProperty("Cookie", "__test=20f6fc07209afd885bd518ce6e3b108d");
                connection.setRequestProperty("Upgrade-Insecure-Requests", "1");
                connection.setRequestProperty("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36");

                // Example post data (can be modified based on your needs)
                String postData = "name=John&age=25";

                // Send request data
                OutputStream os = connection.getOutputStream();
                os.write(postData.getBytes());
                os.flush();
                os.close();

                // Read the response
                int responseCode = connection.getResponseCode();
                if (responseCode == HttpURLConnection.HTTP_OK) {
                    BufferedReader in = new BufferedReader(new InputStreamReader(connection.getInputStream()));
                    String inputLine;
                    StringBuilder response = new StringBuilder();
                    while ((inputLine = in.readLine()) != null) {
                        response.append(inputLine);
                    }
                    in.close();

                    // Parse the JSON response
                    String jsonResponse = response.toString();
                   JSONObject jsonObject = new JSONObject(jsonResponse);

                    // Example: Extract a value from the JSON (adjust based on your JSON structure)
                    String name = jsonObject.getString("data");
                    JSONObject data = new JSONObject(name);

                   int age = data.getInt("age");
                   String na = data.getString("name");

                    // Post the result to the main thread
                    handler.post(() -> {
                        responseTextView.setText("Name: "+na+" Age: "+age);
                        Log.d("RESPONSE", jsonObject.toString());
                    });
                 
                } else {
                    handler.post(() -> responseTextView.setText("Error: " + responseCode));
                }

                connection.disconnect();

            } catch (Exception e) {
                e.printStackTrace();
                handler.post(() -> responseTextView.setText("Failed to get response."));
            }
        });
    }



```

# Singleton Image Fetcher using Curl API
Create a java file named `HttpImageHelper` and write below code. Its fetch image from url and return it as Bitmap image file.
```
package com.nazmulalam.curltest;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.util.Log;

import java.io.BufferedInputStream;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.SecureRandom;
import java.security.cert.X509Certificate;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

public class HttpImageHelper {

    public static Bitmap fetchImage(String urlString, String saveFilePath){
        HttpURLConnection connection = null;
        InputStream inputStream = null;
        FileOutputStream fileOutputStream = null;
        Bitmap image = null;
        try {

            // Set up SSL to bypass certificate validation (equivalent to cURL --insecure)
            TrustManager[] trustAllCerts = new TrustManager[]{
                    new X509TrustManager() {
                        public X509Certificate[] getAcceptedIssuers() {
                            return null;
                        }

                        public void checkClientTrusted(X509Certificate[] certs, String authType) {
                        }

                        public void checkServerTrusted(X509Certificate[] certs, String authType) {
                        }
                    }
            };

            // Install the all-trusting trust manager
            SSLContext sslContext = SSLContext.getInstance("TLS");
            sslContext.init(null, trustAllCerts, new SecureRandom());
            HttpsURLConnection.setDefaultSSLSocketFactory(sslContext.getSocketFactory());

            // Create a hostname verifier that doesn't verify the hostname
            HostnameVerifier allHostsValid = (hostname, session) -> true;
            HttpsURLConnection.setDefaultHostnameVerifier(allHostsValid);


            // Create URL object
            URL url = new URL(urlString);

            // Open connection
            connection = (HttpURLConnection) url.openConnection();

            // Set request method to GET
            connection.setRequestMethod("GET");

            // Set the required headers (including User-Agent)
            connection.setRequestProperty("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7");
            // connection.setRequestProperty("Accept-Language", "en-US,en;q=0.9,bn;q=0.8");
            // connection.setRequestProperty("Connection", "keep-alive");
            connection.setRequestProperty("Cookie", "__test=0467b5866e1eb831a4c1e4d76b59f25c"); // Add your cookie here
            //  connection.setRequestProperty("Sec-Fetch-Dest", "document");
            //   connection.setRequestProperty("Sec-Fetch-Mode", "navigate");
            //   connection.setRequestProperty("Sec-Fetch-Site", "none");
            // connection.setRequestProperty("Sec-Fetch-User", "?1");
            connection.setRequestProperty("Upgrade-Insecure-Requests", "1");
            connection.setRequestProperty("User-Agent", "Mozilla/5.0 (Linux; Android 15; sdk_gphone64_x86_64 Build/AE3A.240806.005; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/129.0.6668.100 Mobile Safari/537.36");
            //   connection.setRequestProperty("sec-ch-ua", "\"Google Chrome\";v=\"129\", \"Not=A?Brand\";v=\"8\", \"Chromium\";v=\"129\"");
            // connection.setRequestProperty("sec-ch-ua-mobile", "?0");
            //  connection.setRequestProperty("sec-ch-ua-platform", "\"Windows\"");

            // Get response code
            int responseCode = connection.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) { // Success

                // Open input stream to read image data
                inputStream = new BufferedInputStream(connection.getInputStream());
                Log.d("ACCD","SUCCESS" + inputStream.toString());
                image = BitmapFactory.decodeStream(inputStream);
                // Open output stream to save the image
                //  fileOutputStream = new FileOutputStream(saveFilePath);

                // Buffer for image data
//                    byte[] buffer = new byte[1024];
//                    int bytesRead;
//                    while ((bytesRead = inputStream.read(buffer)) != -1) {
//                        fileOutputStream.write(buffer, 0, bytesRead);
//                    }

                // Post success message to the main thread (UI update)

//                handler.post(() ->{
//                    imageView.setImageBitmap(image);
//                    Log.d("ACCD","SUCCESS");
//
//                });

            } else {
                // Post failure message to the main thread (UI update)
             //   handler.post(() ->  Log.d("ACCD","Faield" + responseCode));
            }

        } catch (Exception e) {
            // Handle the exception
            e.printStackTrace();
          //  handler.post(() ->  Log.d("ACCD","error"+ e.getMessage()));

        } finally {
            try {
                if (inputStream != null) inputStream.close();
                if (fileOutputStream != null) fileOutputStream.close();
                if (connection != null) connection.disconnect();
            } catch (Exception ex) {
                ex.printStackTrace();
            }
        }

        return image;
    }
}


```
Now, Call the Method on Any activity, As a example on Myactivity that code look like this:
```
public class MainActivity extends AppCompatActivity {
    // Create a background thread executor
     private static final ExecutorService executor = Executors.newSingleThreadExecutor();
    // Create a handler for the main thread
    private static final Handler handler = new Handler(Looper.getMainLooper());

  @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);
        });
  imageView = findViewById(R.id.imageView);
  String imageUrl = "https://nazmulalamshuvo.42web.io/sla/logo.png";

        executor.execute(()->{
            Bitmap image = HttpImageHelper.fetchImage(imageUrl,"null");
            handler.post(() -> {
                imageView.setImageBitmap(image);
                Log.d("RETURNIMG", "Image set");
            });
        });
    }
}
```
If you create this Image fetching Method then use this code. this code also download and save Image also. Just uncomment some code and save image from localstroage. (N.B: Use permission for use local stroage).
```
package com.nazmulalam.curltest;

import android.annotation.SuppressLint;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.util.Log;
import android.webkit.CookieManager;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.activity.EdgeToEdge;
import androidx.annotation.RequiresApi;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import org.json.JSONObject;

import java.io.BufferedInputStream;
import java.io.BufferedReader;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Map;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

public class MainActivity extends AppCompatActivity {
    // Create a background thread executor
    private static final ExecutorService executor = Executors.newSingleThreadExecutor();
    // Create a handler for the main thread
    private static final Handler handler = new Handler(Looper.getMainLooper());
  

    Bitmap imageBitmap;
    static ImageView imageView;
    @SuppressLint("MissingInflatedId")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });
      
        imageView = findViewById(R.id.imageView);

        // Example URL (replace with the URL you provided)
        String imageUrl = "https://nazmulalamshuvo.42web.io/sla/logo.png";

        // File path where the image will be saved
        String saveFilePath = "cat03.jpg";

        // Call the method to fetch and save the image
       fetchImageWithHeaders(imageUrl, saveFilePath);



    }

    // Method to fetch image from a URL (GET or POST) and save it to a file
    public static void fetchImageWithHeaders(String urlString, String saveFilePath) {
        // Run the network operation in the background
        executor.execute(() -> {
            HttpURLConnection connection = null;
            InputStream inputStream = null;
            FileOutputStream fileOutputStream = null;
            Bitmap image;
            try {

                // Set up SSL to bypass certificate validation (equivalent to cURL --insecure)
                TrustManager[] trustAllCerts = new TrustManager[]{
                        new X509TrustManager() {
                            public java.security.cert.X509Certificate[] getAcceptedIssuers() {
                                return null;
                            }

                            public void checkClientTrusted(java.security.cert.X509Certificate[] certs, String authType) {
                            }

                            public void checkServerTrusted(java.security.cert.X509Certificate[] certs, String authType) {
                            }
                        }
                };

                // Install the all-trusting trust manager
                SSLContext sslContext = SSLContext.getInstance("TLS");
                sslContext.init(null, trustAllCerts, new java.security.SecureRandom());
                HttpsURLConnection.setDefaultSSLSocketFactory(sslContext.getSocketFactory());

                // Create a hostname verifier that doesn't verify the hostname
                HostnameVerifier allHostsValid = (hostname, session) -> true;
                HttpsURLConnection.setDefaultHostnameVerifier(allHostsValid);


                // Create URL object
                URL url = new URL(urlString);

                // Open connection
                connection = (HttpURLConnection) url.openConnection();

                // Set request method to GET
                connection.setRequestMethod("GET");

                // Set the required headers (including User-Agent)
                connection.setRequestProperty("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7");
               // connection.setRequestProperty("Accept-Language", "en-US,en;q=0.9,bn;q=0.8");
               // connection.setRequestProperty("Connection", "keep-alive");
                connection.setRequestProperty("Cookie", "__test=0467b5866e1eb831a4c1e4d76b59f25c"); // Add your cookie here
              //  connection.setRequestProperty("Sec-Fetch-Dest", "document");
             //   connection.setRequestProperty("Sec-Fetch-Mode", "navigate");
             //   connection.setRequestProperty("Sec-Fetch-Site", "none");
               // connection.setRequestProperty("Sec-Fetch-User", "?1");
                connection.setRequestProperty("Upgrade-Insecure-Requests", "1");
                connection.setRequestProperty("User-Agent", "Mozilla/5.0 (Linux; Android 15; sdk_gphone64_x86_64 Build/AE3A.240806.005; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/129.0.6668.100 Mobile Safari/537.36");
             //   connection.setRequestProperty("sec-ch-ua", "\"Google Chrome\";v=\"129\", \"Not=A?Brand\";v=\"8\", \"Chromium\";v=\"129\"");
               // connection.setRequestProperty("sec-ch-ua-mobile", "?0");
              //  connection.setRequestProperty("sec-ch-ua-platform", "\"Windows\"");

                // Get response code
                int responseCode = connection.getResponseCode();
                if (responseCode == HttpURLConnection.HTTP_OK) { // Success

                    // Open input stream to read image data
                    inputStream = new BufferedInputStream(connection.getInputStream());
                    Log.d("ACCD","SUCCESS" + inputStream.toString());
                    image = BitmapFactory.decodeStream(inputStream);
                    // Open output stream to save the image
                  //  fileOutputStream = new FileOutputStream(saveFilePath);

                    // Buffer for image data
//                    byte[] buffer = new byte[1024];
//                    int bytesRead;
//                    while ((bytesRead = inputStream.read(buffer)) != -1) {
//                        fileOutputStream.write(buffer, 0, bytesRead);
//                    }

                    // Post success message to the main thread (UI update)
                    handler.post(() ->{
                        imageView.setImageBitmap(image);
                        Log.d("ACCD","SUCCESS");

                    });

                } else {
                    // Post failure message to the main thread (UI update)
                    handler.post(() ->  Log.d("ACCD","Faield" + responseCode));
                }

            } catch (Exception e) {
                // Handle the exception
                e.printStackTrace();
                handler.post(() ->  Log.d("ACCD","error"+ e.getMessage()));

            } finally {
                try {
                    if (inputStream != null) inputStream.close();
                    if (fileOutputStream != null) fileOutputStream.close();
                    if (connection != null) connection.disconnect();
                } catch (Exception ex) {
                    ex.printStackTrace();
                }
            }
        });

    }
}
```
Details::

To create a simple Java method that can handle both `GET` and `POST` requests and return a JSON response, you'll need to:

1. **Create a helper class** where the `getRequest()` method is defined. This method should accept a `url` and optional `postDataString` (for `POST` requests), send the request, and return the response as a string.

2. **Use `HttpURLConnection`** to perform both `GET` and `POST` requests.

3. **Return the JSON response** in a string format, which can then be parsed as necessary in the calling activity or class.

Here’s how you can structure this in a Java helper class for your Android project:

### Step 1: Create a Helper Class
Create a new Java class, for example, `HttpHelper.java`.

```java
import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class HttpHelper {

    // Method to send a GET or POST request and return a JSON response string
    public static String getRequest(String urlString, String postDataString) {
        HttpURLConnection connection = null;
        try {
            // Create a URL object
            URL url = new URL(urlString);

            // Open the connection
            connection = (HttpURLConnection) url.openConnection();

            // If postDataString is null, it's a GET request; otherwise, it's a POST request
            if (postDataString == null || postDataString.isEmpty()) {
                connection.setRequestMethod("GET");
            } else {
                connection.setRequestMethod("POST");
                connection.setDoOutput(true);  // Indicates POST request

                // Send POST data
                DataOutputStream outputStream = new DataOutputStream(connection.getOutputStream());
                outputStream.writeBytes(postDataString);
                outputStream.flush();
                outputStream.close();
            }

            // Set headers
            connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
            connection.setRequestProperty("User-Agent", "Mozilla/5.0");

            // Get response code
            int responseCode = connection.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) { // Success
                BufferedReader in = new BufferedReader(new InputStreamReader(connection.getInputStream()));
                String inputLine;
                StringBuilder response = new StringBuilder();

                // Read the response
                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }
                in.close();

                // Return the response as a string
                return response.toString();
            } else {
                return "Error: " + responseCode;
            }
        } catch (Exception e) {
            e.printStackTrace();
            return "Exception: " + e.getMessage();
        } finally {
            if (connection != null) {
                connection.disconnect();
            }
        }
    }
}
```

### Step 2: How to Use the Helper Method in Your Activity

Now, you can call this `getRequest()` method from any `Activity` or Java class. Here’s how to use it:

```java
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class MainActivity extends AppCompatActivity {

    private TextView responseTextView;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    private final Handler handler = new Handler(Looper.getMainLooper());

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        responseTextView = findViewById(R.id.response_text_view);

        // Example usage of the helper method
        String url = "https://your-server.com/api.php";
        String postData = "name=John&age=25";  // Post data can be null for GET requests

        // Make a request in a background thread to avoid blocking the UI
        executor.execute(() -> {
            // Use the helper class method to get a response from the server
            String jsonResponse = HttpHelper.getRequest(url, postData); // Can pass null for GET request

            // Post the result back to the main thread to update the UI
            handler.post(() -> responseTextView.setText(jsonResponse));
        });
    }
}
```

### Key Points:
1. **GET Request**: If you want to make a `GET` request, simply pass `null` for `postDataString`.

   ```java
   String jsonResponse = HttpHelper.getRequest("https://example.com/api", null);
   ```

2. **POST Request**: If you want to make a `POST` request, you can pass the data as a URL-encoded string:

   ```java
   String postData = "name=John&age=25";
   String jsonResponse = HttpHelper.getRequest("https://example.com/api", postData);
   ```

3. **JSON Response**: The response will be returned as a `String`, which can then be parsed using libraries like `JSONObject` or `Gson` to extract the needed data.

### Step 3: Parse the JSON Response (Optional)
If the server returns a JSON object and you want to parse it:

```java
try {
    JSONObject jsonObject = new JSONObject(jsonResponse);
    String name = jsonObject.getString("name");
    int age = jsonObject.getInt("age");
    
    // Update UI or perform other operations with the parsed data
} catch (JSONException e) {
    e.printStackTrace();
}
```

This setup allows you to send both `GET` and `POST` requests using one method and access the JSON response from any activity. You should always perform the network requests on a background thread (as demonstrated) to avoid blocking the main UI thread.
