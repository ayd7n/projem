#!/bin/bash
# CURL test script for the order item operations API

echo "Testing AJAX endpoint for adding order items..."
echo

# Test the API endpoints with CURL
echo "1. Testing GET request for product info (should fail without proper params):"
curl -X GET "http://localhost/projem/api_islemleri/order_item_operations.php?action=get_product_info" -v
echo
echo "----------------------------------------"
echo

echo "2. Testing POST request for adding order item (should fail without proper params):"
curl -X POST "http://localhost/projem/api_islemleri/order_item_operations.php" -d "action=add_order_item" -v
echo
echo "----------------------------------------"
echo

echo "3. Testing POST request for updating order item (should fail without proper params):"
curl -X POST "http://localhost/projem/api_islemleri/order_item_operations.php" -d "action=update_order_item" -v
echo
echo "----------------------------------------"
echo

echo "4. Testing POST request for deleting order item (should fail without proper params):"
curl -X POST "http://localhost/projem/api_islemleri/order_item_operations.php" -d "action=delete_order_item" -v
echo
echo "----------------------------------------"
echo

echo "Tests completed. Note: These tests expect failures without proper authentication and parameters."