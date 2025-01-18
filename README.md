# **AI Prompt Tool**
A Laravel backend tool demonstrating AI prompt engineering expertise.

---

## **Technologies Used**
- **Laravel**: Backend framework.
- **MongoDB**: NoSQL database for efficient data storage and retrieval.
- **OpenAI's GPT**: AI-powered language model for generating responses.
- **Aylien API**: Sentiment analysis for evaluating response tone.


## **Project Setup**

### **1. MongoDB Configuration**

#### **Step 1**: Update `config/database.php`

Add the following configuration under `'connections'`:

```php
'connections' => [
    'mongodb' => [
        'driver' => 'mongodb',
        'dsn' => env('MONGODB_URI', 'mongodb://localhost:27017'),
        'database' => env('MONGODB_DATABASE', 'laravel_app'),
    ],
],
'default' => env('DB_CONNECTION', 'mongodb'),
```

#### **Step 2**: Update `.env`

Add the following parameters:

```bash
$ DB_CONNECTION=mongodb
$ MONGODB_URI="mongodb://localhost:27017"
$ MONGODB_DATABASE="db_tester"
```


### **2. OpenAI's GPT Setup**

#### Steps to Generate API Key

- Visit the OpenAI API Keys Page.
- Log in and click Create new secret key.
- Copy the key immediately as it won't be shown again.
- Add the API key to your .env file

```bash
$ OPENAI_API_KEY=your_generated_api_key
```


### **3. Aylien API Setup**

#### Steps to Obtain API Credentials

- Go to the Aylien API website and sign up for an account.
- Obtain your API key and application ID.
- Update .env File

```bash
$ AYLIEN_APP_ID=your_app_id
$ AYLIEN_APP_KEY=your_app_key
```