# Service API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication
Some endpoints require authentication using Laravel Sanctum tokens.

## Endpoints

### 1. Get All Services
**GET** `/services`

Get a paginated list of all services with optional filtering.

**Query Parameters:**
- `available` (boolean): Filter by availability
- `category_id` (integer): Filter by category ID
- `min_price` (decimal): Minimum price filter
- `max_price` (decimal): Maximum price filter
- `min_duration` (integer): Minimum duration in minutes
- `max_duration` (integer): Maximum duration in minutes
- `search` (string): Search in name, description, benefits
- `per_page` (integer): Items per page (default: 15)

**Example Request:**
```
GET /api/services?available=true&category_id=1&min_price=50&search=hair
```

**Example Response:**
```json
{
  "success": true,
  "message": "Services retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Premium Hair Cut",
      "description": "Professional hair cutting with style consultation",
      "benefits": "Fresh new look, personalized styling advice",
      "price": {
        "amount": 75.00,
        "formatted": "$75.00"
      },
      "duration": {
        "minutes": 60,
        "formatted": "1 hr"
      },
      "is_available": true,
      "stylist_qualifications": "Certified hair stylists with 3+ years experience",
      "image_url": null,
      "category": {
        "id": 1,
        "name": "Hair Services"
      },
      "created_at": "2025-08-03T07:53:55.000000Z",
      "updated_at": "2025-08-03T07:53:55.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 9,
    "from": 1,
    "to": 9
  }
}
```

### 2. Get Specific Service
**GET** `/services/{id}`

**Example Response:**
```json
{
  "success": true,
  "message": "Service retrieved successfully",
  "data": {
    "id": 1,
    "name": "Premium Hair Cut",
    "description": "Professional hair cutting with style consultation",
    "benefits": "Fresh new look, personalized styling advice",
    "price": {
      "amount": 75.00,
      "formatted": "$75.00"
    },
    "duration": {
      "minutes": 60,
      "formatted": "1 hr"
    },
    "is_available": true,
    "stylist_qualifications": "Certified hair stylists with 3+ years experience",
    "image_url": null,
    "category": {
      "id": 1,
      "name": "Hair Services"
    },
    "stylists": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@salon.com",
        "is_active": true
      }
    ]
  }
}
```

### 3. Create Service (Protected)
**POST** `/services`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "category_id": 1,
  "name": "New Service",
  "description": "Service description",
  "benefits": "Service benefits",
  "price": 85.00,
  "duration": 90,
  "is_available": true,
  "stylist_qualifications": "Required qualifications",
  "image_url": "https://example.com/image.jpg"
}
```

### 4. Update Service (Protected)
**PUT** `/services/{id}`

Same request format as create.

### 5. Delete Service (Protected)
**DELETE** `/services/{id}`

### 6. Get Services by Duration
**GET** `/services/by-duration/{duration}`

Get services that can be completed within the specified duration.

### 7. Get Services by Price Range
**GET** `/services/by-price?min=50&max=100`

### 8. Get Service Categories
**GET** `/services/categories`

**Example Response:**
```json
{
  "success": true,
  "message": "Categories retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Hair Services",
      "services_count": 3
    }
  ]
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error message",
  "error": "Detailed error information",
  "errors": {
    "field": ["Validation error messages"]
  }
}
```

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error
- `401` - Unauthorized

## Usage for Teammates

### Booking Service Integration
```javascript
// Get service details for booking
const response = await fetch('/api/services/1');
const service = await response.json();

// Use service.data.price.amount for booking cost
// Use service.data.duration.minutes for time slot calculation
```

### Stylist Service Integration
```javascript
// Get services by stylist capabilities
const response = await fetch('/api/services?category_id=1&available=true');
const services = await response.json();

// Display available services for stylist specialization
```